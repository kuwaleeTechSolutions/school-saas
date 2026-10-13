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
use App\Domain\Tenant\Services\AttendanceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section 31/41 — the end-to-end proof that Stage 3's AttendanceService
 * event hook and Stage 6's listener are correctly wired together: marking
 * a student absent via the real AttendanceService (unchanged from Stage 3)
 * must result in an in-app Notification for that student's guardian,
 * without AttendanceService itself knowing anything about notifications.
 */
class AbsenceNotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function marking_a_student_absent_creates_a_notification_for_their_guardian(): void
    {
        $this->seed();

        $school = School::factory()->create();
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($school);

        $session = AcademicSession::factory()->current()->create(['school_id' => $school->id]);
        $class = SchoolClass::factory()->create(['school_id' => $school->id, 'academic_session_id' => $session->id]);
        $section = Section::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);

        $student = Student::factory()->create(['school_id' => $school->id]);
        $student->enrollments()->create([
            'school_id' => $school->id, 'academic_session_id' => $session->id,
            'class_id' => $class->id, 'section_id' => $section->id, 'status' => 'active',
        ]);

        $guardianUser = User::factory()->forSchool($school)->create();
        $guardian = Guardian::create(['school_id' => $school->id, 'user_id' => $guardianUser->id, 'name' => 'Parent', 'phone' => '123']);
        $guardian->students()->attach($student->id, ['relation' => 'father', 'is_primary_contact' => true]);

        $teacherUser = User::factory()->forSchool($school)->create();

        $attendanceService = app(AttendanceService::class);
        $attendanceService->takeAttendance($section, Carbon::parse('2026-06-01'), 0, [$student->id => 'absent'], $teacherUser);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $guardianUser->id,
            'type' => 'attendance.absent',
        ]);
    }
}
