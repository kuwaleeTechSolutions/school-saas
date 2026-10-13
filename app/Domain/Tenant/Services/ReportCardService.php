<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\Exam;
use App\Domain\Tenant\Models\Grade;
use App\Domain\Tenant\Models\Mark;
use App\Domain\Tenant\Models\ReportCard;
use App\Domain\Tenant\Models\Student;
use App\Jobs\GenerateReportCardPdf;
use App\Models\User;
use RuntimeException;

class ReportCardService
{
    public function __construct(protected AuditService $audit) {}

    public function generate(Exam $exam, Student $student, User $actor): ReportCard
    {
        if (! $exam->isFullyPublished()) {
            throw new RuntimeException('Report cards can only be generated once every subject in this exam has been published.');
        }
        $marks = Mark::whereHas('examSubject', fn($q) => $q->where('exam_id', $exam->id))
            ->where('student_id', $student->id)->with('examSubject.subject')->get();

        $subjectRows = $marks->map(function (Mark $mark) use ($student) {
            $grade = $mark->percent() !== null ? Grade::forPercent($mark->percent(), $student->school_id) : null;
            return [
                'subject' => $mark->examSubject->subject->name, 'max_marks' => (float) $mark->examSubject->max_marks,
                'marks_obtained' => $mark->is_absent ? null : (float) $mark->marks_obtained,
                'is_absent' => $mark->is_absent, 'percent' => $mark->percent(), 'grade' => $grade?->label, 'passed' => $mark->isPassing(),
            ];
        })->values()->all();

        $totalObtained = $marks->where('is_absent', false)->sum('marks_obtained');
        $totalMax = $marks->sum(fn(Mark $m) => (float) $m->examSubject->max_marks);
        $overallPercent = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null;
        $overallGrade = $overallPercent !== null ? Grade::forPercent($overallPercent, $student->school_id) : null;
        $attendancePercent = $this->attendancePercentFor($student, $exam);

        $snapshot = [
            'student_name' => $student->fullName(), 'admission_number' => $student->admission_number,
            'class_section' => optional($student->currentEnrollment)->schoolClass?->name . ' - ' . optional($student->currentEnrollment)->section?->name,
            'exam_name' => $exam->name, 'subjects' => $subjectRows, 'total_obtained' => (float) $totalObtained,
            'total_max' => (float) $totalMax, 'attendance_percent' => $attendancePercent, 'generated_at' => now()->toIso8601String(),
        ];

        $reportCard = ReportCard::updateOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['snapshot' => $snapshot, 'overall_grade' => $overallGrade?->label, 'overall_percent' => $overallPercent, 'pdf_status' => 'pending']
        );
        $this->audit->logSchool($actor->id, 'report_card.generated', ReportCard::class, $reportCard->id);
        return $reportCard;
    }

    public function queueBulkGeneration(Exam $exam, array $studentIds, User $actor): void
    {
        foreach ($studentIds as $studentId) {
            $student = Student::findOrFail($studentId);
            $reportCard = $this->generate($exam, $student, $actor);
            $reportCard->update(['pdf_status' => 'queued']);
            GenerateReportCardPdf::dispatch($reportCard->id);
        }
    }

    protected function attendancePercentFor(Student $student, Exam $exam): ?float
    {
        if (! $exam->ends_on) return null;
        $sessionStart = $exam->academicSession?->starts_on;
        if (! $sessionStart) return null;
        $total = AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereBetween('attendance_date', [$sessionStart, $exam->ends_on]))->count();
        if ($total === 0) return null;
        $present = AttendanceRecord::where('student_id', $student->id)->whereIn('status', ['present','late','half_day'])
            ->whereHas('session', fn($q) => $q->whereBetween('attendance_date', [$sessionStart, $exam->ends_on]))->count();
        return round(($present / $total) * 100, 2);
    }
}
