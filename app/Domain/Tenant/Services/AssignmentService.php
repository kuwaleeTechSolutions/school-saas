<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Assignment;
use App\Domain\Tenant\Models\AssignmentSubmission;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Models\User;

class AssignmentService
{
    public function __construct(protected AuditService $audit) {}

    public function assign(array $attributes, User $actor): Assignment
    {
        $assignment = Assignment::create($attributes);
        $studentIds = StudentEnrollment::where('section_id', $assignment->section_id)->where('status', 'active')->pluck('student_id');
        foreach ($studentIds as $studentId) {
            AssignmentSubmission::create(['assignment_id' => $assignment->id, 'student_id' => $studentId, 'status' => 'pending']);
        }
        $this->audit->logSchool($actor->id, 'assignment.assigned', Assignment::class, $assignment->id, null, ['title' => $assignment->title, 'student_count' => $studentIds->count()]);
        return $assignment;
    }

    public function submit(AssignmentSubmission $submission, ?string $attachmentPath, ?string $note, Student $student): AssignmentSubmission
    {
        $isLate = $submission->assignment->due_date->isPast();
        $submission->update(['attachment_path' => $attachmentPath, 'student_note' => $note, 'submitted_at' => now(), 'status' => $isLate ? 'late' : 'submitted']);
        return $submission->fresh();
    }

    public function grade(AssignmentSubmission $submission, float $marksObtained, ?string $comment, User $actor): AssignmentSubmission
    {
        $old = $submission->only(['marks_obtained', 'status']);
        $submission->update(['marks_obtained' => $marksObtained, 'teacher_comment' => $comment, 'status' => 'graded']);
        $this->audit->logSchool($actor->id, 'assignment.graded', AssignmentSubmission::class, $submission->id, $old, ['marks_obtained' => $marksObtained]);
        return $submission->fresh();
    }
}
