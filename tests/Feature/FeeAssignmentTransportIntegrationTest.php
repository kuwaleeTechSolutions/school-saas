<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\TransportRoute;
use App\Domain\Tenant\Services\FeeAssignmentService;
use App\Domain\Tenant\Services\TransportService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section 32/45 — the end-to-end proof that Stage 7's
 * student_transport_assignments table and the STAGE 7 OVERLAY replacement
 * of FeeAssignmentService genuinely connect: a `requires_transport` fee
 * structure must be ignored for a student with no transport assignment,
 * and must correctly activate the moment TransportService assigns them a
 * route — all without FeeAssignmentService's specificity-scoring logic
 * (unchanged since Stage 5) needing to change at all.
 */
class FeeAssignmentTransportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicSession $session;
    protected Student $student;
    protected FeeStructure $transportStructure;
    protected FeeStructure $defaultStructure;
    protected User $actor;

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

        $this->session = AcademicSession::factory()->current()->create(['school_id' => $this->school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id]);
        $section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $class->id]);

        $this->student = Student::factory()->create(['school_id' => $this->school->id]);
        $this->student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $this->session->id,
            'class_id' => $class->id, 'section_id' => $section->id, 'status' => 'active',
        ]);

        $this->defaultStructure = FeeStructure::create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id, 'name' => 'Default', 'is_active' => true]);
        $this->transportStructure = FeeStructure::create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id, 'name' => 'Transport Riders', 'requires_transport' => true, 'is_active' => true]);

        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function before_any_transport_assignment_the_transport_structure_never_matches(): void
    {
        $service = app(FeeAssignmentService::class);
        $resolved = $service->resolveBestStructure($this->student, $this->session->id);

        $this->assertSame($this->defaultStructure->id, $resolved->id);
    }

    /** @test */
    public function after_a_transport_assignment_the_transport_structure_correctly_activates(): void
    {
        $route = TransportRoute::create(['school_id' => $this->school->id, 'name' => 'Route 1', 'monthly_fee' => 500, 'is_active' => true]);

        $transportService = app(TransportService::class);
        $transportService->assignStudent($this->student, $route, $this->session->id, null, $this->actor);

        $feeService = app(FeeAssignmentService::class);
        $resolved = $feeService->resolveBestStructure($this->student, $this->session->id);

        $this->assertSame($this->transportStructure->id, $resolved->id);
    }

    /** @test */
    public function unassigning_transport_reverts_to_the_default_structure(): void
    {
        $route = TransportRoute::create(['school_id' => $this->school->id, 'name' => 'Route 1', 'monthly_fee' => 500, 'is_active' => true]);

        $transportService = app(TransportService::class);
        $transportService->assignStudent($this->student, $route, $this->session->id, null, $this->actor);
        $transportService->unassignStudent($this->student, $this->session->id, $this->actor);

        $feeService = app(FeeAssignmentService::class);
        $resolved = $feeService->resolveBestStructure($this->student, $this->session->id);

        $this->assertSame($this->defaultStructure->id, $resolved->id);
    }

    /** @test */
    public function a_transport_assignment_in_a_different_session_does_not_activate_the_current_sessions_structure(): void
    {
        $otherSession = AcademicSession::factory()->create(['school_id' => $this->school->id, 'name' => 'Other Session', 'is_current' => false]);
        $route = TransportRoute::create(['school_id' => $this->school->id, 'name' => 'Route 1', 'monthly_fee' => 500, 'is_active' => true]);

        $transportService = app(TransportService::class);
        $transportService->assignStudent($this->student, $route, $otherSession->id, null, $this->actor);

        $feeService = app(FeeAssignmentService::class);
        $resolved = $feeService->resolveBestStructure($this->student, $this->session->id);

        $this->assertSame($this->defaultStructure->id, $resolved->id, 'A transport assignment from a different session must never leak into this session\'s fee resolution.');
    }
}
