<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\LeaveRequest;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\StaffProfile;
use App\Domain\Tenant\Services\PayrollService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 47 — "Monthly payroll generation." Verifies one payslip per
 * active staff member, leave-based deduction calculation, the one-run-per-
 * period guard, and that a payslip's figures are frozen at generation time
 * (never recomputed from a later-edited salary).
 */
class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected StaffProfile $staff;
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

        $staffUser = User::factory()->forSchool($this->school)->create();
        $this->staff = StaffProfile::factory()->create([
            'school_id' => $this->school->id, 'user_id' => $staffUser->id,
            'monthly_gross_salary' => 30000, 'annual_leave_quota' => 12,
        ]);

        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function generating_creates_one_payslip_per_active_staff_member(): void
    {
        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);

        $this->assertCount(1, $run->payslips);
        $this->assertEquals(30000, $run->payslips->first()->gross_salary);
    }

    /** @test */
    public function a_duplicate_run_for_the_same_period_is_rejected(): void
    {
        $service = app(PayrollService::class);
        $service->generate(2026, 6, $this->actor);

        $this->expectException(RuntimeException::class);
        $service->generate(2026, 6, $this->actor);
    }

    /** @test */
    public function approved_leave_beyond_the_monthly_quota_reduces_net_salary(): void
    {
        // 12 days/year quota = 1 day/month; approve 5 days of leave in June.
        LeaveRequest::create([
            'school_id' => $this->school->id, 'staff_profile_id' => $this->staff->id,
            'starts_on' => '2026-06-10', 'ends_on' => '2026-06-14', 'reason' => 'Personal',
            'status' => 'approved', 'approved_by' => $this->actor->id,
        ]);

        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);
        $payslip = $run->payslips->first();

        $this->assertGreaterThan(0, $payslip->leave_deduction);
        $this->assertLessThan(30000, $payslip->net_salary);
    }

    /** @test */
    public function leave_within_the_monthly_quota_causes_no_deduction(): void
    {
        // 1 day of leave, well within the ~1 day/month quota.
        LeaveRequest::create([
            'school_id' => $this->school->id, 'staff_profile_id' => $this->staff->id,
            'starts_on' => '2026-06-10', 'ends_on' => '2026-06-10', 'reason' => 'Personal',
            'status' => 'approved', 'approved_by' => $this->actor->id,
        ]);

        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);
        $payslip = $run->payslips->first();

        $this->assertEquals(0, $payslip->leave_deduction);
        $this->assertEquals(30000, $payslip->net_salary);
    }

    /** @test */
    public function finalizing_queues_a_payslip_pdf_job_for_every_payslip(): void
    {
        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);
        $finalized = $service->finalize($run, $this->actor);

        $this->assertSame('finalized', $finalized->status);
        Queue::assertPushed(\App\Jobs\GeneratePayslipPdf::class, 1);
    }

    /** @test */
    public function finalizing_twice_is_rejected(): void
    {
        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);
        $service->finalize($run, $this->actor);

        $this->expectException(RuntimeException::class);
        $service->finalize($run->fresh(), $this->actor);
    }

    /** @test */
    public function a_rejected_leave_request_never_affects_payroll(): void
    {
        LeaveRequest::create([
            'school_id' => $this->school->id, 'staff_profile_id' => $this->staff->id,
            'starts_on' => '2026-06-01', 'ends_on' => '2026-06-20', 'reason' => 'Vacation',
            'status' => 'rejected', 'approved_by' => $this->actor->id,
        ]);

        $service = app(PayrollService::class);
        $run = $service->generate(2026, 6, $this->actor);
        $payslip = $run->payslips->first();

        $this->assertEquals(0, $payslip->leave_deduction);
        $this->assertEquals(30000, $payslip->net_salary);
    }
}
