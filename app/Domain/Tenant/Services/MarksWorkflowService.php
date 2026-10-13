<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\ExamSubject;
use App\Domain\Tenant\Models\Mark;
use App\Domain\Tenant\Models\MarkApprovalHistory;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Events\ExamResultsPublished;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * STAGE 6 OVERLAY NOTE: this file replaces Stage 4's
 * MarksWorkflowService.php wholesale. The only change from Stage 4 is in
 * transition(): once a subject reaches 'published', it now checks whether
 * the WHOLE exam has just become fully published and, if so, fires
 * ExamResultsPublished exactly once — the hook point Stage 6's
 * SendResultsPublishedNotification listener attaches to. Every other
 * method is unchanged from Stage 4.
 *
 * Section 37 — "Teacher enters marks -> Teacher submits -> Class teacher
 * verifies -> Exam coordinator verifies -> Principal approves -> Publish.
 * Once published, modifications require authorized permission. All
 * modifications audited."
 */
class MarksWorkflowService
{
    protected const TRANSITIONS = [
        'pending' => ['entered'],
        'entered' => ['submitted'],
        'submitted' => ['class_teacher_verified'],
        'class_teacher_verified' => ['coordinator_verified'],
        'coordinator_verified' => ['principal_approved'],
        'principal_approved' => ['published'],
    ];

    public function __construct(protected AuditService $audit)
    {
    }

    public function availableNextStatuses(ExamSubject $examSubject): array
    {
        return self::TRANSITIONS[$examSubject->status] ?? [];
    }

    public function canTransition(ExamSubject $examSubject, string $toStatus): bool
    {
        return in_array($toStatus, $this->availableNextStatuses($examSubject), true);
    }

    public function ensureMarkRowsExist(ExamSubject $examSubject): void
    {
        $studentIds = StudentEnrollment::where('class_id', $examSubject->exam->class_id)
            ->where('academic_session_id', $examSubject->exam->academic_session_id)
            ->where('status', 'active')
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            Mark::firstOrCreate(['exam_subject_id' => $examSubject->id, 'student_id' => $studentId]);
        }
    }

    public function enterMarks(ExamSubject $examSubject, array $entries, User $actor): void
    {
        if (! in_array($examSubject->status, ['pending', 'entered'], true)) {
            throw new RuntimeException('Marks can only be entered while the subject is in draft (not yet submitted for approval).');
        }

        DB::transaction(function () use ($examSubject, $entries, $actor) {
            foreach ($entries as $entry) {
                Mark::updateOrCreate(
                    ['exam_subject_id' => $examSubject->id, 'student_id' => $entry['student_id']],
                    ['marks_obtained' => $entry['is_absent'] ? null : $entry['marks_obtained'], 'is_absent' => $entry['is_absent'], 'entered_by' => $actor->id]
                );
            }

            if ($examSubject->status === 'pending') {
                $this->transition($examSubject, 'entered', $actor);
            }
        });
    }

    public function transition(ExamSubject $examSubject, string $toStatus, User $actor, ?string $notes = null): ExamSubject
    {
        if (! $this->canTransition($examSubject, $toStatus)) {
            throw new RuntimeException("Cannot move marks from '{$examSubject->status}' to '{$toStatus}'.");
        }

        $fromStatus = $examSubject->status;

        MarkApprovalHistory::create([
            'school_id' => $examSubject->school_id,
            'exam_subject_id' => $examSubject->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $actor->id,
            'notes' => $notes,
            'created_at' => now(),
        ]);

        $examSubject->update(['status' => $toStatus]);

        $this->audit->logSchool($actor->id, 'marks.status_changed', ExamSubject::class, $examSubject->id, ['status' => $fromStatus], ['status' => $toStatus]);

        // Stage 6 addition: fire the results-published hook exactly once,
        // the moment the LAST subject in this exam reaches 'published'.
        if ($toStatus === 'published') {
            $exam = $examSubject->exam()->first();
            if ($exam && $exam->isFullyPublished()) {
                event(new ExamResultsPublished($exam));
            }
        }

        return $examSubject->fresh();
    }

    public function correctPublishedMark(Mark $mark, ?float $newMarksObtained, bool $newIsAbsent, User $actor, string $reason): Mark
    {
        $old = $mark->only(['marks_obtained', 'is_absent']);

        $mark->update(['marks_obtained' => $newIsAbsent ? null : $newMarksObtained, 'is_absent' => $newIsAbsent]);

        $this->audit->logSchool($actor->id, 'marks.corrected_after_publish', Mark::class, $mark->id, $old, [
            'marks_obtained' => $mark->marks_obtained, 'is_absent' => $mark->is_absent, 'reason' => $reason,
        ]);

        return $mark->fresh();
    }
}
