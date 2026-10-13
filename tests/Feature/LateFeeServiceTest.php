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
use App\Domain\Tenant\Models\LateFeeRule;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Services\InvoiceGenerationService;
use App\Domain\Tenant\Services\LateFeeService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section 33/34 — late fee calculation respects the configured grace
 * period, each calculation_type (fixed / fixed_per_day / percentage_per_day),
 * and the optional max_amount cap; a non-overdue invoice never accrues a
 * late fee regardless of what the rule says.
 */
class LateFeeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

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
            'installment_number' => 1, 'label' => 'Full Year', 'percentage_of_total' => 100, 'due_date' => Carbon::parse('2026-01-01'),
        ]);

        StudentFeeAssignment::create([
            'school_id' => $this->school->id, 'student_id' => $student->id, 'academic_session_id' => $session->id,
            'fee_structure_id' => $structure->id, 'fee_installment_plan_id' => $installmentPlan->id,
        ]);

        $actor = User::factory()->forSchool($this->school)->create();
        $invoiceService = app(InvoiceGenerationService::class);
        $this->invoice = $invoiceService->generateForInstallment($student, $installment->id, $actor);
    }

    /** @test */
    public function no_late_fee_accrues_before_the_due_date(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 0, 'calculation_type' => 'fixed', 'value' => 500, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2025-12-25'));

        $this->assertEquals(0.0, $fee);
    }

    /** @test */
    public function no_late_fee_accrues_within_the_grace_period(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 5, 'calculation_type' => 'fixed', 'value' => 500, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-01-03')); // 2 days overdue, within 5-day grace

        $this->assertEquals(0.0, $fee);
    }

    /** @test */
    public function a_fixed_late_fee_applies_once_past_the_grace_period(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 2, 'calculation_type' => 'fixed', 'value' => 500, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-01-10'));

        $this->assertEquals(500.0, $fee);
    }

    /** @test */
    public function a_fixed_per_day_rule_scales_with_days_overdue(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 0, 'calculation_type' => 'fixed_per_day', 'value' => 10, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-01-06')); // 5 days overdue

        $this->assertEquals(50.0, $fee);
    }

    /** @test */
    public function a_percentage_per_day_rule_is_computed_against_the_invoice_subtotal(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 0, 'calculation_type' => 'percentage_per_day', 'value' => 1, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-01-06')); // 5 days overdue, 1%/day of 10000

        $this->assertEquals(500.0, $fee); // 10000 * 0.01 * 5
    }

    /** @test */
    public function max_amount_caps_the_computed_late_fee(): void
    {
        LateFeeRule::create(['school_id' => $this->school->id, 'grace_days' => 0, 'calculation_type' => 'fixed_per_day', 'value' => 100, 'max_amount' => 300, 'is_active' => true]);

        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-01-11')); // 10 days * 100 = 1000, capped to 300

        $this->assertEquals(300.0, $fee);
    }

    /** @test */
    public function no_active_rule_means_no_late_fee_regardless_of_how_overdue(): void
    {
        $service = app(LateFeeService::class);
        $fee = $service->computeLateFee($this->invoice, Carbon::parse('2026-06-01'));

        $this->assertEquals(0.0, $fee);
    }
}
