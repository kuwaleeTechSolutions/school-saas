<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Livewire\Tenant\ParentPortal\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;

/**
 * Section G.4 — "own_children_only" scope. Verifies a Guardian's Parent
 * Portal dashboard shows ONLY their own linked children's names, never
 * another guardian's child, even within the same school.
 */
class ParentPortalScopeTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicSession $session;
    protected SchoolClass $class;
    protected Section $section;

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
        URL::defaults(['school_slug' => $this->school->slug]);

        $this->session = AcademicSession::factory()->current()->create(['school_id' => $this->school->id]);
        $this->class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id]);
        $this->section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $this->class->id]);
    }

    protected function enrollChild(string $firstName): Student
    {
        $student = Student::factory()->create(['school_id' => $this->school->id, 'first_name' => $firstName]);
        $student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $this->session->id,
            'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active',
        ]);

        return $student;
    }

    /** @test */
    public function a_guardian_sees_only_their_own_childs_name_never_another_guardians_child(): void
    {
        $myChild = $this->enrollChild('MyChildFirstName');
        $otherChild = $this->enrollChild('OtherChildFirstName');

        $parentRole = Role::where('name', 'Parent')->whereNull('school_id')->first();

        $myUser = User::factory()->forSchool($this->school)->create();
        $myUser->assignRole($parentRole);
        $myGuardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $myUser->id, 'name' => 'Me', 'phone' => '1']);
        $myGuardian->students()->attach($myChild->id, ['relation' => 'father', 'is_primary_contact' => true]);

        $otherUser = User::factory()->forSchool($this->school)->create();
        $otherGuardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $otherUser->id, 'name' => 'Other', 'phone' => '2']);
        $otherGuardian->students()->attach($otherChild->id, ['relation' => 'mother', 'is_primary_contact' => true]);

        $this->actingAs($myUser);

        Livewire::test(Dashboard::class)
            ->assertSee('MyChildFirstName')
            ->assertDontSee('OtherChildFirstName');
    }

    /** @test */
    public function a_user_with_no_guardian_profile_is_forbidden(): void
    {
        $userWithoutGuardian = User::factory()->forSchool($this->school)->create();
        $this->actingAs($userWithoutGuardian);

        Livewire::test(Dashboard::class)->assertForbidden();
    }
}
