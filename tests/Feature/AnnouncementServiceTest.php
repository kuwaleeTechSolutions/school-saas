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
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Services\AnnouncementService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Section 41 — "Announcements: school-wide or targeted by class/section/
 * role." Verifies audience resolution is computed from CURRENT
 * enrollment/assignment data for each audience type, and that
 * applicableToQuery() (the inverse — "what can THIS user see") agrees with
 * resolveAudience() ("who should see THIS announcement").
 */
class AnnouncementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected AcademicSession $session;
    protected SchoolClass $class;
    protected Section $section;
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
        $this->class = SchoolClass::factory()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id]);
        $this->section = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $this->class->id]);

        $this->actor = User::factory()->forSchool($this->school)->create();
        $ownerRole = Role::where('name', 'School Owner')->whereNull('school_id')->first();
        $this->actor->assignRole($ownerRole);
    }

    protected function enrolledGuardianUser(): User
    {
        $student = Student::factory()->create(['school_id' => $this->school->id]);
        $student->enrollments()->create([
            'school_id' => $this->school->id, 'academic_session_id' => $this->session->id,
            'class_id' => $this->class->id, 'section_id' => $this->section->id, 'status' => 'active',
        ]);

        $guardianUser = User::factory()->forSchool($this->school)->create();
        $parentRole = Role::where('name', 'Parent')->whereNull('school_id')->first();
        $guardianUser->assignRole($parentRole);

        $guardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $guardianUser->id, 'name' => 'Test Guardian', 'phone' => '1234567890']);
        $guardian->students()->attach($student->id, ['relation' => 'father', 'is_primary_contact' => true]);

        return $guardianUser;
    }

    /** @test */
    public function a_school_wide_announcement_reaches_every_active_user(): void
    {
        $guardianUser = $this->enrolledGuardianUser();
        $service = app(AnnouncementService::class);

        $announcement = $service->publish(['title' => 'Holiday', 'body' => 'School closed tomorrow.', 'audience_type' => 'school_wide', 'audience_filters' => null], $this->actor);

        $audience = $service->resolveAudience($announcement);
        $this->assertTrue($audience->contains('id', $guardianUser->id));
        $this->assertTrue($audience->contains('id', $this->actor->id));
    }

    /** @test */
    public function a_class_targeted_announcement_only_reaches_that_class_guardians(): void
    {
        $inClassGuardian = $this->enrolledGuardianUser();

        $otherClass = SchoolClass::factory()->create([
            'school_id' => $this->school->id,
            'academic_session_id' => $this->session->id,
            'name' => 'Other Class',
        ]);
        $otherSection = Section::factory()->create(['school_id' => $this->school->id, 'class_id' => $otherClass->id]);
        $otherStudent = Student::factory()->create(['school_id' => $this->school->id]);
        $otherStudent->enrollments()->create(['school_id' => $this->school->id, 'academic_session_id' => $this->session->id, 'class_id' => $otherClass->id, 'section_id' => $otherSection->id, 'status' => 'active']);
        $outsideGuardianUser = User::factory()->forSchool($this->school)->create();
        $outsideGuardian = Guardian::create(['school_id' => $this->school->id, 'user_id' => $outsideGuardianUser->id, 'name' => 'Outside Guardian', 'phone' => '000']);
        $outsideGuardian->students()->attach($otherStudent->id, ['relation' => 'mother', 'is_primary_contact' => true]);

        $service = app(AnnouncementService::class);
        $announcement = $service->publish(['title' => 'Class Trip', 'body' => 'Field trip details.', 'audience_type' => 'class', 'audience_filters' => ['class_id' => $this->class->id]], $this->actor);

        $audience = $service->resolveAudience($announcement);
        $this->assertTrue($audience->contains('id', $inClassGuardian->id));
        $this->assertFalse($audience->contains('id', $outsideGuardianUser->id));
    }

    /** @test */
    public function a_role_targeted_announcement_reaches_only_that_role(): void
    {
        $teacherUser = User::factory()->forSchool($this->school)->create();
        $teacherRole = Role::where('name', 'Teacher')->whereNull('school_id')->first();
        $teacherUser->assignRole($teacherRole);
        Teacher::create(['school_id' => $this->school->id, 'user_id' => $teacherUser->id, 'status' => 'active', 'joining_date' => now()]);

        $guardianUser = $this->enrolledGuardianUser();

        $service = app(AnnouncementService::class);
        $announcement = $service->publish(['title' => 'Staff Meeting', 'body' => 'Tomorrow at 4pm.', 'audience_type' => 'role', 'audience_filters' => ['role' => 'Teacher']], $this->actor);

        $audience = $service->resolveAudience($announcement);
        $this->assertTrue($audience->contains('id', $teacherUser->id));
        $this->assertFalse($audience->contains('id', $guardianUser->id));
    }

    /** @test */
    public function applicable_to_query_agrees_with_resolve_audience_for_a_guardian(): void
    {
        $guardianUser = $this->enrolledGuardianUser();
        $service = app(AnnouncementService::class);

        $service->publish(['title' => 'Class Notice', 'body' => 'Notice body.', 'audience_type' => 'class', 'audience_filters' => ['class_id' => $this->class->id]], $this->actor);

        $visible = $service->applicableToQuery($guardianUser)->pluck('title')->all();
        $this->assertContains('Class Notice', $visible);
    }

    /** @test */
    public function publishing_dispatches_an_in_app_notification_to_every_recipient(): void
    {
        $guardianUser = $this->enrolledGuardianUser();
        $service = app(AnnouncementService::class);

        $service->publish(['title' => 'Holiday', 'body' => 'School closed.', 'audience_type' => 'school_wide', 'audience_filters' => null], $this->actor);

        $this->assertDatabaseHas('notifications', ['user_id' => $guardianUser->id, 'type' => 'announcement.published']);
    }
}
