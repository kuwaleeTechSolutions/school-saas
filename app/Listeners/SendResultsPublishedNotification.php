<?php

namespace App\Listeners;

use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Services\NotificationService;
use App\Events\ExamResultsPublished;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Section 37/41 — notifies every guardian (with a linked login) of every
 * student enrolled in the exam's class that results are now available.
 */
class SendResultsPublishedNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    public function handle(ExamResultsPublished $event): void
    {
        $exam = $event->exam;

        $studentIds = StudentEnrollment::where('class_id', $exam->class_id)
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('status', 'active')
            ->pluck('student_id');

        $guardians = Guardian::whereHas('students', fn ($q) => $q->whereIn('students.id', $studentIds))
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        foreach ($guardians as $guardian) {
            if (! $guardian->user) {
                continue;
            }

            $this->notifications->dispatch(
                $guardian->user,
                'results.published',
                'Results Published: ' . $exam->name,
                "Results for {$exam->name} have been published. You can now view the report card.",
                ['exam_id' => $exam->id],
            );
        }
    }
}
