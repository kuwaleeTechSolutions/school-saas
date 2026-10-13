<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Announcement;
use App\Domain\Tenant\Models\AnnouncementRead;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

/**
 * Section 41 — "Announcements: school-wide or targeted by class/section/
 * role." `resolveAudience()` is the single place that turns an
 * `audience_type` + `audience_filters` pair into an actual recipient list —
 * computed fresh at publish time from CURRENT enrollment/assignment data,
 * never from a snapshot, so a student transferred out of a class after an
 * announcement was drafted correctly stops being notified about it.
 */
class AnnouncementService
{
    public function __construct(protected AuditService $audit, protected NotificationService $notifications)
    {
    }

    public function publish(array $attributes, User $actor, bool $alsoEmail = false): Announcement
    {
        $announcement = Announcement::create(array_merge($attributes, [
            'published_by' => $actor->id,
            'published_at' => now(),
        ]));

        $recipients = $this->resolveAudience($announcement);

        foreach ($recipients as $recipient) {
            $this->notifications->dispatch(
                $recipient,
                'announcement.published',
                $announcement->title,
                $announcement->body,
                ['announcement_id' => $announcement->id],
                $alsoEmail ? ['email'] : [],
            );
        }

        $this->audit->logSchool($actor->id, 'announcement.published', Announcement::class, $announcement->id, null, [
            'title' => $announcement->title, 'audience_type' => $announcement->audience_type, 'recipient_count' => $recipients->count(),
        ]);

        return $announcement;
    }

    /** @return Collection<int, User> */
    public function resolveAudience(Announcement $announcement): Collection
    {
        return match ($announcement->audience_type) {
            'school_wide' => User::where('status', 'active')->get(),
            'class' => $this->audienceForClass($announcement->audience_filters['class_id'] ?? null),
            'section' => $this->audienceForSection($announcement->audience_filters['section_id'] ?? null),
            'role' => $this->audienceForRole($announcement->audience_filters['role'] ?? null),
            default => collect(),
        };
    }

    protected function audienceForClass(?int $classId): Collection
    {
        if (! $classId) {
            return collect();
        }

        return $this->guardianUsersForEnrollments(
            StudentEnrollment::where('class_id', $classId)->where('status', 'active')
        )->merge($this->teacherUsersForAssignments(
            TeacherSubjectAssignment::where('class_id', $classId)
        ))->unique('id')->values();
    }

    protected function audienceForSection(?int $sectionId): Collection
    {
        if (! $sectionId) {
            return collect();
        }

        return $this->guardianUsersForEnrollments(
            StudentEnrollment::where('section_id', $sectionId)->where('status', 'active')
        )->merge($this->teacherUsersForAssignments(
            TeacherSubjectAssignment::where('section_id', $sectionId)
        ))->unique('id')->values();
    }

    protected function audienceForRole(?string $roleName): Collection
    {
        if (! $roleName) {
            return collect();
        }

        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            return collect();
        }

        return User::role($role->name)->where('status', 'active')->get();
    }

    protected function guardianUsersForEnrollments($enrollmentQuery): Collection
    {
        $studentIds = $enrollmentQuery->pluck('student_id');

        $guardianUserIds = Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $studentIds))
            ->whereNotNull('user_id')
            ->pluck('user_id');

        return User::whereIn('id', $guardianUserIds)->where('status', 'active')->get();
    }

    protected function teacherUsersForAssignments($assignmentQuery): Collection
    {
        $teacherUserIds = $assignmentQuery->with('teacher')->get()->pluck('teacher.user_id')->filter();

        return User::whereIn('id', $teacherUserIds)->where('status', 'active')->get();
    }

    /**
     * The inverse of resolveAudience(): "every published announcement that
     * CURRENTLY applies to this user," used by Announcements\Index. Built
     * from the same live enrollment/assignment/role data, so a Guardian or
     * Teacher only ever sees announcements genuinely relevant to them right
     * now — never a stale list.
     */
    public function applicableToQuery(User $user): Builder
    {
        $roleNames = $user->roles()->pluck('name')->all();

        $classIds = collect();
        $sectionIds = collect();

        $guardian = Guardian::where('user_id', $user->id)->first();
        if ($guardian) {
            $studentIds = $guardian->students()->pluck('students.id');
            $enrollments = StudentEnrollment::whereIn('student_id', $studentIds)->where('status', 'active')->get();
            $classIds = $classIds->merge($enrollments->pluck('class_id'));
            $sectionIds = $sectionIds->merge($enrollments->pluck('section_id'));
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        if ($teacher) {
            $assignments = TeacherSubjectAssignment::where('teacher_id', $teacher->id)->get();
            $classIds = $classIds->merge($assignments->pluck('class_id'));
            $sectionIds = $sectionIds->merge($assignments->pluck('section_id')->filter());
        }

        return Announcement::whereNotNull('published_at')
            ->where(function (Builder $q) use ($roleNames, $classIds, $sectionIds) {
                $q->where('audience_type', 'school_wide')
                    ->orWhere(function (Builder $q2) use ($roleNames) {
                        $q2->where('audience_type', 'role')->whereIn('audience_filters->role', $roleNames);
                    })
                    ->orWhere(function (Builder $q2) use ($classIds) {
                        $q2->where('audience_type', 'class')->whereIn('audience_filters->class_id', $classIds->all());
                    })
                    ->orWhere(function (Builder $q2) use ($sectionIds) {
                        $q2->where('audience_type', 'section')->whereIn('audience_filters->section_id', $sectionIds->all());
                    });
            });
    }

    public function markRead(Announcement $announcement, User $user): void
    {
        AnnouncementRead::firstOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['read_at' => now()]
        );
    }
}
