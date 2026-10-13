<?php

namespace App\Listeners;

use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Services\NotificationService;
use App\Events\StudentMarkedAbsent;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Section 41/31 — the listener Stage 3/4's AttendanceService hook
 * (`event(new StudentMarkedAbsent($record))`) was always meant to connect
 * to; that event already existed from Stage 3 specifically so this Stage 6
 * listener could be added without any change to AttendanceService itself.
 * Notifies every guardian who has a linked login — a guardian record
 * without `user_id` set (no portal login yet) simply cannot be notified
 * in-app or by email until one exists; SMS/WhatsApp (phone-number-based,
 * no login required) remain Stage 8 gateway work per
 * LoggedStubChannel's docblock.
 */
class SendAbsenceNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    public function handle(StudentMarkedAbsent $event): void
    {
        $student = $event->record->student;

        $guardians = Guardian::whereHas('students', fn ($q) => $q->where('students.id', $student->id))
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        foreach ($guardians as $guardian) {
            if (! $guardian->user) {
                continue;
            }

            $this->notifications->dispatch(
                $guardian->user,
                'attendance.absent',
                'Attendance: ' . $student->fullName() . ' marked absent',
                $student->fullName() . ' was marked absent on ' . $event->record->session->attendance_date->format('d M Y') . '.',
                ['student_id' => $student->id, 'attendance_record_id' => $event->record->id],
            );
        }
    }
}
