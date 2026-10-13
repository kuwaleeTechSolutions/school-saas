<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PromotionService
{
    public function __construct(protected AuditService $audit) {}
    public function promoteBatch(AcademicSession $fromSession, AcademicSession $toSession, array $decisions, User $actor): string
    {
        if ($fromSession->id === $toSession->id) throw new RuntimeException('The target session must be different from the current session.');
        $batchUlid = (string) Str::ulid();
        DB::transaction(function () use ($fromSession, $toSession, $decisions, $actor, $batchUlid) {
            foreach ($decisions as $row) $this->applyDecision($fromSession, $toSession, $row, $actor, $batchUlid);
        });
        $this->audit->logSchool($actor->id, 'students.promoted', null, null, null, ['batch'=>$batchUlid,'from_session'=>$fromSession->name,'to_session'=>$toSession->name,'count'=>count($decisions)]);
        return $batchUlid;
    }
    protected function applyDecision(AcademicSession $fromSession, AcademicSession $toSession, array $row, User $actor, string $batchUlid): void
    {
        $student = Student::findOrFail($row['student_id']);
        $fromEnrollment = StudentEnrollment::where('student_id', $student->id)->where('academic_session_id', $fromSession->id)->firstOrFail();
        $decision = $row['decision'];
        StudentPromotion::create(['student_id'=>$student->id,'from_academic_session_id'=>$fromSession->id,'from_class_id'=>$fromEnrollment->class_id,'from_section_id'=>$fromEnrollment->section_id,'to_academic_session_id'=>$toSession->id,'to_class_id'=>$row['to_class_id'] ?? null,'to_section_id'=>$row['to_section_id'] ?? null,'decision'=>$decision,'promoted_by'=>$actor->id,'batch_ulid'=>$batchUlid]);
        match ($decision) {
            'promoted', 'retained' => $this->createNextEnrollment($student, $toSession, $row),
            'graduated' => $this->finalizeEnrollment($fromEnrollment, $student, 'completed', 'alumni'),
            'withdrawn' => $this->finalizeEnrollment($fromEnrollment, $student, 'withdrawn', 'inactive'),
            default => throw new RuntimeException("Unknown promotion decision '{$decision}'."),
        };
    }
    protected function createNextEnrollment(Student $student, AcademicSession $toSession, array $row): void
    {
        if (empty($row['to_class_id']) || empty($row['to_section_id'])) throw new RuntimeException("A target class/section is required to promote or retain {$student->fullName()}.");
        StudentEnrollment::updateOrCreate(['student_id'=>$student->id,'academic_session_id'=>$toSession->id], ['class_id'=>$row['to_class_id'],'section_id'=>$row['to_section_id'],'status'=>'active']);
    }
    protected function finalizeEnrollment($enrollment, Student $student, string $enrollmentStatus, string $studentStatus): void
    {
        $enrollment->update(['status' => $enrollmentStatus]); $student->update(['status' => $studentStatus]);
    }
}
