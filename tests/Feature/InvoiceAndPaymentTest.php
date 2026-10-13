<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\FeeComponent;
use App\Domain\Tenant\Models\FeeInstallment;
use App\Domain\Tenant\Models\FeeInstallmentPlan;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\FeeStructureItem;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Scholarship;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Models\StudentScholarship;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use App\Domain\Tenant\Services\PaymentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 33/34 — invoice generation produces one component-level line
 * item per structure item (never one opaque total), applies scholarship
 * discounts as their own negative line item, and is idempotent per
 * student+installment. Payments correctly transition unpaid ->
 * partially_paid -> paid, and an overpayment is rejected rather than
 * silently accepted.
 */
class InvoiceAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicSession $session;
    protected Student $student;
    protected FeeInstallment $installment;
    protected User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Queue::fake();

        $this->school = School::factory()->create();
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $this->school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($this->school);

        $this->session = AcademicSession::factory()->current()->create(['school_id' => $this->school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id]);
        $section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $class->id]);

        $this->student = Student::factory()->create(['school_id' => $this->school->id]);
        $this->student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $this->session->id,
            'class_id' => $class->id, 'section_id' => $section->id, 'status' => 'active',
        ]);

        $structure = FeeStructure::create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id, 'name' => 'Default', 'is_active' => true]);
        $tuition = FeeComponent::create(['school_id' => $this->school->id, 'name' => 'Tuition', 'category' => 'tuition']);
        $library = FeeComponent::create(['school_id' => $this->school->id, 'name' => 'Library', 'category' => 'library']);
        FeeStructureItem::create(['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'fee_component_id' => $tuition->id, 'annual_amount' => 10000]);
        FeeStructureItem::create(['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'fee_component_id' => $library->id, 'annual_amount' => 2000]);

        $installmentPlan = FeeInstallmentPlan::create(['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'name' => 'Annual', 'frequency' => 'annual']);
        $this->installment = FeeInstallment::create([
            'school_id' => $this->school->id, 'fee_installment_plan_id' => $installmentPlan->id,
            'installment_number' => 1, 'label' => 'Full Year', 'percentage_of_total' => 100, 'due_date' => now()->addDays(30),
        ]);

        StudentFeeAssignment::create([
            'school_id' => $this->school->id, 'student_id' => $this->student->id, 'academic_session_id' => $this->session->id,
            'fee_structure_id' => $structure->id, 'fee_installment_plan_id' => $installmentPlan->id,
        ]);

        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function generating_an_invoice_creates_one_line_item_per_component(): void
    {
        $service = app(InvoiceGenerationService::class);
        $invoice = $service->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $this->assertCount(2, $invoice->lineItems);
        $this->assertEquals(12000, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);
    }

    /** @test */
    public function generating_twice_for_the_same_installment_does_not_duplicate_the_invoice(): void
    {
        $service = app(InvoiceGenerationService::class);
        $first = $service->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $second = $service->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, FeeInvoice::count());
    }

    /** @test */
    public function a_scholarship_is_applied_as_its_own_discount_line_item(): void
    {
        $scholarship = Scholarship::create(['school_id' => $this->school->id, 'name' => 'Merit', 'discount_type' => 'percentage', 'value' => 10]);
        StudentScholarship::create(['school_id' => $this->school->id, 'student_id' => $this->student->id, 'scholarship_id' => $scholarship->id, 'academic_session_id' => $this->session->id]);
        $service = app(InvoiceGenerationService::class);
        $invoice = $service->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $this->assertEquals(1200, $invoice->discount_amount);
        $this->assertEquals(10800, $invoice->total_amount);
        $this->assertTrue($invoice->lineItems->contains(fn($i) => $i->line_type === 'discount' && $i->amount == -1200));
    }

    /** @test */
    public function a_partial_payment_moves_the_invoice_to_partially_paid(): void
    {
        $invoiceService = app(InvoiceGenerationService::class);
        $invoice = $invoiceService->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $paymentService = app(PaymentService::class);
        $paymentService->recordPayment($invoice, 5000, 'cash', $this->actor);
        $invoice = $invoice->fresh();
        $this->assertEquals('partially_paid', $invoice->status);
        $this->assertEquals(5000, $invoice->paid_amount);
        $this->assertEquals(7000, $invoice->balance_amount);
    }

    /** @test */
    public function paying_the_full_balance_moves_the_invoice_to_paid(): void
    {
        $invoiceService = app(InvoiceGenerationService::class);
        $invoice = $invoiceService->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $paymentService = app(PaymentService::class);
        $paymentService->recordPayment($invoice, 12000, 'cash', $this->actor);
        $invoice = $invoice->fresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->balance_amount);
    }

    /** @test */
    public function a_payment_exceeding_the_balance_is_rejected(): void
    {
        $invoiceService = app(InvoiceGenerationService::class);
        $invoice = $invoiceService->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $paymentService = app(PaymentService::class);
        $this->expectException(RuntimeException::class);
        $paymentService->recordPayment($invoice, 99999, 'cash', $this->actor);
    }

    /** @test */
    public function recording_a_payment_queues_a_receipt(): void
    {
        $invoiceService = app(InvoiceGenerationService::class);
        $invoice = $invoiceService->generateForInstallment($this->student, $this->installment->id, $this->actor);
        $paymentService = app(PaymentService::class);
        $payment = $paymentService->recordPayment($invoice, 12000, 'cash', $this->actor);
        $this->assertNotNull($payment->receipt);
        $this->assertSame('queued', $payment->receipt->pdf_status);
    }
}
