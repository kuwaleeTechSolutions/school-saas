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
use App\Domain\Tenant\Services\FeeAssignmentService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 32 — "Fee rules can depend on: class, section, category,
 * transport, hostel, scholarship, student type." Verifies the most
 * specific matching fee_structure always wins over a school-wide default.
 */
class FeeAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicSession $session;
    protected SchoolClass $class;
    protected Section $section;
    protected Student $student;
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
        $this->class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id]);
        $this->section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $this->class->id]);

        $this->student = Student::factory()->create(['school_id' => $this->school->id]);
        $this->student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $this->session->id,
            'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active',
        ]);

        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    protected function makeStructure(array $overrides = []): FeeStructure
    {
        return FeeStructure::create(array_merge(['academic_session_id' => $this->session->id, 'name' => 'Structure', 'is_active' => true], $overrides));
    }

    /** @test */
    public function a_school_wide_structure_matches_when_nothing_more_specific_exists(): void
    {
        $structure = $this->makeStructure(['name' => 'School-wide default']);
        $service = app(FeeAssignmentService::class);
        $resolved = $service->resolveBestStructure($this->student, $this->session->id);
        $this->assertSame($structure->id, $resolved->id);
    }

    /** @test */
    public function a_class_specific_structure_outranks_a_school_wide_default(): void
    {
        $this->makeStructure(['name' => 'School-wide default']);
        $classSpecific = $this->makeStructure(['name' => 'Class specific', 'class_id' => $this->class->id]);
        $service = app(FeeAssignmentService::class);
        $resolved = $service->resolveBestStructure($this->student, $this->session->id);
        $this->assertSame($classSpecific->id, $resolved->id);
    }

    /** @test */
    public function a_class_and_section_specific_structure_outranks_a_class_only_one(): void
    {
        $this->makeStructure(['name' => 'Class only', 'class_id' => $this->class->id]);
        $classAndSection = $this->makeStructure(['name' => 'Class+Section', 'class_id' => $this->class->id, 'section_id' => $this->section->id]);
        $service = app(FeeAssignmentService::class);
        $resolved = $service->resolveBestStructure($this->student, $this->session->id);
        $this->assertSame($classAndSection->id, $resolved->id);
    }

    /** @test */
    public function a_transport_only_structure_never_matches_a_student_without_transport(): void
    {
        $this->makeStructure(['name' => 'Transport-only', 'requires_transport' => true]);
        $default = $this->makeStructure(['name' => 'Default']);
        $service = app(FeeAssignmentService::class);
        $resolved = $service->resolveBestStructure($this->student, $this->session->id);
        $this->assertSame($default->id, $resolved->id);
    }

    /** @test */
    public function assign_fails_loudly_when_no_structure_matches_at_all(): void
    {
        $service = app(FeeAssignmentService::class);
        $this->expectException(RuntimeException::class);
        $service->assign($this->student, $this->session->id, 1, $this->actor);
    }

    /** @test */
    public function assign_writes_a_student_fee_assignment_row(): void
    {
        $structure = $this->makeStructure();
        $component = FeeComponent::create(['school_id' => $this->school->id, 'name' => 'Tuition', 'category' => 'tuition']);
        FeeStructureItem::create(['fee_structure_id' => $structure->id, 'fee_component_id' => $component->id, 'annual_amount' => 12000]);
        $plan = FeeInstallmentPlan::create(['fee_structure_id' => $structure->id, 'name' => 'Annual', 'frequency' => 'annual']);
        FeeInstallment::create(['fee_installment_plan_id' => $plan->id, 'installment_number' => 1, 'label' => 'Full', 'percentage_of_total' => 100, 'due_date' => now()->addDays(30)]);
        $service = app(FeeAssignmentService::class);
        $assignment = $service->assign($this->student, $this->session->id, $plan->id, $this->actor);
        $this->assertSame($structure->id, $assignment->fee_structure_id);
        $this->assertSame($plan->id, $assignment->fee_installment_plan_id);
    }
}
