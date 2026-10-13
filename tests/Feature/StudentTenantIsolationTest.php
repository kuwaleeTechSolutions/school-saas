<?php
namespace Tests\Feature;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Livewire\Tenant\Student\Index as StudentIndex;
use App\Livewire\Tenant\Student\Show as StudentShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;

class StudentTenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    protected School $schoolA; protected School $schoolB; protected User $userA; protected Student $studentA; protected Student $studentB;
    protected function setUp(): void
    {
        parent::setUp(); $this->seed();
        $this->schoolA = $this->buildSchoolWithData('school-a-students');
        $this->schoolB = $this->buildSchoolWithData('school-b-students');
        $this->studentA = $this->studentFor($this->schoolA);
        $this->studentB = $this->studentFor($this->schoolB);
        $ownerRole = Role::where('name', 'School Owner')->whereNull('school_id')->first();
        $this->userA = User::factory()->forSchool($this->schoolA)->create();
        app(TenantContext::class)->set($this->schoolA);
        $this->userA->assignRole($ownerRole);
    }
    protected function bindTenant(School $school): void
    {
        app(TenantContext::class)->set($school);

        URL::defaults([
            'school_slug' => $school->slug,
        ]);
    }
    protected function buildSchoolWithData(string $slug): School
    {
        $school = School::factory()->create(['slug' => $slug]);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        $this->bindTenant($school);
        $session = AcademicSession::factory()->current()->create(['school_id' => $school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $school->id, 'academic_session_id' => $session->id]);
        Section::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        return $school;
    }
    protected function studentFor(School $school): Student
    {
        $this->bindTenant($school);
        $section = Section::first();
        $student = Student::factory()->create(['school_id' => $school->id]);
        $student->enrollments()->create(['school_id'=>$school->id,'academic_session_id'=>$school->activeAcademicSession()->id,'class_id'=>$section->class_id,'section_id'=>$section->id,'status'=>'active']);
        return $student;
    }
    /** @test */
    public function student_list_never_includes_another_schools_students(): void
    {
        $this->bindTenant($this->schoolA);
        $ids = Student::pluck('id')->all();
        $this->assertContains($this->studentA->id, $ids);
        $this->assertNotContains($this->studentB->id, $ids);
    }
    /** @test */
    public function find_by_id_across_schools_returns_null(): void
    {
        $this->bindTenant($this->schoolA);
        $this->assertNull(Student::find($this->studentB->id));
    }
    /** @test */
    public function viewing_another_schools_student_by_guessed_id_fails(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->actingAs($this->userA);
        $this->get("http://{$this->schoolA->slug}.localhost/students/{$this->studentB->id}")->assertNotFound();
    }
    /** @test */
    /** @test */
    public function livewire_show_component_cannot_be_mounted_with_another_schools_student_id(): void
    {
        $this->bindTenant($this->schoolA);
        $this->actingAs($this->userA);

        $this->get(
            "http://{$this->schoolA->slug}.localhost/students/{$this->studentB->id}"
        )->assertNotFound();
    }

    /** @test */
    public function student_index_component_only_ever_lists_the_bound_schools_students(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->actingAs($this->userA);
        Livewire::test(StudentIndex::class)->assertSee($this->studentA->admission_number)->assertDontSee($this->studentB->admission_number);
    }
}
