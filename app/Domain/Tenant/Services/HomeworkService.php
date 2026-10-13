<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Homework;
use App\Domain\Tenant\Models\HomeworkSubmission;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Models\User;

class HomeworkService
{
    public function __construct(protected AuditService $audit) {}

    public function assign(array $attributes, User $actor): Homework
    {
        $homework = Homework::create($attributes);
        $studentIds = StudentEnrollment::where('section_id', $homework->section_id)->where('status', 'active')->pluck('student_id');
        foreach ($studentIds as $studentId) {
            HomeworkSubmission::create(['homework_id' => $homework->id, 'student_id' => $studentId, 'status' => 'pending']);
        }
        $this->audit->logSchool($actor->id, 'homework.assigned', Homework::class, $homework->id, null, ['title' => $homework->title, 'student_count' => $studentIds->count()]);
        return $homework;
    }

    public function submit(HomeworkSubmission $submission, ?string $attachmentPath, ?string $note, Student $student): HomeworkSubmission
    {
        $isLate = $submission->homework->due_date->isPast();
        $submission->update(['attachment_path' => $attachmentPath, 'student_note' => $note, 'submitted_at' => now(), 'status' => $isLate ? 'late' : 'submitted']);
        return $submission->fresh();
    }

    public function grade(HomeworkSubmission $submission, float $gradePoints, ?string $comment, User $actor): HomeworkSubmission
    {
        $old = $submission->only(['grade_points', 'status']);
        $submission->update(['grade_points' => $gradePoints, 'teacher_comment' => $comment, 'status' => 'graded']);
        $this->audit->logSchool($actor->id, 'homework.graded', HomeworkSubmission::class, $submission->id, $old, ['grade_points' => $gradePoints]);
        return $submission->fresh();
    }
}
