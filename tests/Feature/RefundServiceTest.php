<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\FeeComponent;
use App\Domain\Tenant\Models\FeeInstallment;
use App\Domain\Tenant\Models\FeeInstallmentPlan;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\FeeStructureItem;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use App\Domain\Tenant\Services\PaymentService;
use App\Domain\Tenant\Services\RefundService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 7/34 — a refund is request -> approve -> process, never an
 * instant single-step action (mirrors Admissions/Marks approval chains).
 * Verifies the invoice's paid/balance amounts are correctly reversed only
 * once a refund is actually processed, never merely requested.
 */
class RefundServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $actor;
    protected $invoice;
    protected $payment;

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

        $session = AcademicSession::factory()->current()->create(['school_id' => $this->school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $session->id]);
        $section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $class->id]);

        $student = Student::factory()->create(['school_id' => $this->school->id]);
        $student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $session->id,
            'class_id' => $class->id, 'section_id' => $section->id, 'status' => 'active',
        ]);

        $structure = FeeStructure::create(['school_id' => $this->school->id, 'academic_session_id' => $session->id, 'name' => 'Default', 'is_active' => true]);
        $component = FeeComponent::create(['school_id' => $this->school->id, 'name' => 'Tuition', 'category' => 'tuition']);
        FeeStructureItem::create(['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'fee_component_id' => $component->id, 'annual_amount' => 10000]);

        $installmentPlan = FeeInstallmentPlan::create(['school_id' => $this->school->id, 'fee_structure_id' => $structure->id, 'name' => 'Annual', 'frequency' => 'annual']);
        $installment = FeeInstallment::create([
            'school_id' => $this->school->id, 'fee_installment_plan_id' => $installmentPlan->id,
            'installment_number' => 1, 'label' => 'Full Year', 'percentage_of_total' => 100, 'due_date' => now()->addDays(30),
        ]);

        StudentFeeAssignment::create([
            'school_id' => $this->school->id, 'student_id' => $student->id, 'academic_session_id' => $session->id,
            'fee_structure_id' => $structure->id, 'fee_installment_plan_id' => $installmentPlan->id,
        ]);

        $this->actor = User::factory()->forSchool($this->school)->create();

        $invoiceService = app(InvoiceGenerationService::class);
        $this->invoice = $invoiceService->generateForInstallment($student, $installment->id, $this->actor);

        $paymentService = app(PaymentService::class);
        $this->payment = $paymentService->recordPayment($this->invoice, 10000, 'cash', $this->actor);
    }

    /** @test */
    public function requesting_a_refund_does_not_change_the_invoice_balance(): void
    {
        $service = app(RefundService::class);
        $service->request($this->payment, 3000, 'Parent requested partial refund', $this->actor);

        $this->assertEquals(0, $this->invoice->fresh()->balance_amount, 'Balance must remain untouched until the refund is actually processed.');
    }

    /** @test */
    public function approving_and_processing_reverses_the_invoice_paid_amount(): void
    {
        $service = app(RefundService::class);
        $refund = $service->request($this->payment, 3000, 'Parent requested partial refund', $this->actor);
        $service->approveAndProcess($refund, $this->actor);

        $invoice = $this->invoice->fresh();
        $this->assertEquals(7000, $invoice->paid_amount);
        $this->assertEquals(3000, $invoice->balance_amount);
        $this->assertEquals('partially_paid', $invoice->status);
    }

    /** @test */
    public function rejecting_a_refund_never_touches_the_invoice(): void
    {
        $service = app(RefundService::class);
        $refund = $service->request($this->payment, 3000, 'Reason', $this->actor);
        $service->reject($refund, 'Not eligible per policy', $this->actor);

        $this->assertEquals(0, $this->invoice->fresh()->balance_amount);
        $this->assertEquals('rejected', $refund->fresh()->status);
    }

    /** @test */
    public function requesting_more_than_the_refundable_amount_is_rejected(): void
    {
        $service = app(RefundService::class);

        $this->expectException(RuntimeException::class);
        $service->request($this->payment, 20000, 'Too much', $this->actor);
    }

    /** @test */
    public function a_refund_cannot_be_approved_twice(): void
    {
        $service = app(RefundService::class);
        $refund = $service->request($this->payment, 3000, 'Reason', $this->actor);
        $service->approveAndProcess($refund, $this->actor);

        $this->expectException(RuntimeException::class);
        $service->approveAndProcess($refund->fresh(), $this->actor);
    }
}
