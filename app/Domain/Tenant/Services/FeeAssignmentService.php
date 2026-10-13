<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Models\StudentTransportAssignment;
use App\Models\User;
use RuntimeException;

/**
 * STAGE 7 OVERLAY NOTE: replaces Stage 5's FeeAssignmentService.php
 * wholesale. ONLY hasTransportAssignment() changed — now a real lookup
 * against student_transport_assignments instead of unconditionally false.
 */
class FeeAssignmentService
{
    public function __construct(protected AuditService $audit) {}

    public function resolveBestStructure(Student $student, int $academicSessionId): ?FeeStructure
    {
        $enrollment = $student->enrollments()->where('academic_session_id', $academicSessionId)->first();
        if (! $enrollment) return null;
        $hasTransport = $this->hasTransportAssignment($student->id, $academicSessionId);
        $candidates = FeeStructure::where('academic_session_id', $academicSessionId)->where('is_active', true)
            ->where(fn($q) => $q->whereNull('class_id')->orWhere('class_id', $enrollment->class_id))
            ->where(fn($q) => $q->whereNull('section_id')->orWhere('section_id', $enrollment->section_id))
            ->where(fn($q) => $q->whereNull('student_category')->orWhere('student_category', $student->category))
            ->get()
            ->filter(fn(FeeStructure $s) => ! ($s->requires_transport && ! $hasTransport));
        return $candidates->sortByDesc(fn(FeeStructure $s) => $s->specificityScore())->first();
    }

    protected function hasTransportAssignment(int $studentId, int $academicSessionId): bool
    {
        return StudentTransportAssignment::where('student_id', $studentId)->where('academic_session_id', $academicSessionId)->exists();
    }

    public function assign(Student $student, int $academicSessionId, int $feeInstallmentPlanId, User $actor): StudentFeeAssignment
    {
        $structure = $this->resolveBestStructure($student, $academicSessionId);
        if (! $structure) {
            throw new RuntimeException('No matching fee structure found for this student. Create one for their class/session first.');
        }
        $assignment = StudentFeeAssignment::updateOrCreate(
            ['student_id' => $student->id, 'academic_session_id' => $academicSessionId],
            ['fee_structure_id' => $structure->id, 'fee_installment_plan_id' => $feeInstallmentPlanId]
        );
        $this->audit->logSchool($actor->id, 'fee_assignment.created', StudentFeeAssignment::class, $assignment->id, null, [
            'student_id' => $student->id, 'fee_structure_id' => $structure->id,
        ]);
        return $assignment;
    }
}
