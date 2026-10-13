<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\AttendanceCorrection;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\AttendanceSession;
use App\Domain\Tenant\Models\Holiday;
use App\Domain\Tenant\Models\Section;
use App\Events\StudentMarkedAbsent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AttendanceService
{
    public function __construct(protected AuditService $audit) {}

    public function takeAttendance(Section $section, Carbon $date, int $periodNumber, array $statuses, User $actor): AttendanceSession
    {
        if (Holiday::where('holiday_date', $date->toDateString())->exists()) {
            throw new RuntimeException('Attendance cannot be taken on a declared holiday.');
        }
        $existing = AttendanceSession::where('class_id', $section->class_id)->where('section_id', $section->id)
            ->where('attendance_date', $date->toDateString())->where('period_number', $periodNumber)->first();
        if ($existing && $existing->locked_at) {
            throw new RuntimeException('Attendance for this date/period is locked. Use a correction instead of retaking it.');
        }
        return DB::transaction(function () use ($section, $date, $periodNumber, $statuses, $actor, $existing) {
            $session = $existing ?? AttendanceSession::create([
                'academic_session_id' => $section->schoolClass->academic_session_id, 'class_id' => $section->class_id,
                'section_id' => $section->id, 'attendance_date' => $date->toDateString(),
                'period_number' => $periodNumber, 'taken_by' => $actor->id,
            ]);
            foreach ($statuses as $studentId => $status) {
                $record = AttendanceRecord::updateOrCreate(
                    ['attendance_session_id' => $session->id, 'student_id' => $studentId], ['status' => $status]
                );
                if ($status === 'absent') event(new StudentMarkedAbsent($record));
            }
            $this->audit->logSchool($actor->id, 'attendance.taken', AttendanceSession::class, $session->id, null, [
                'class_id' => $section->class_id, 'section_id' => $section->id, 'date' => $date->toDateString(), 'period' => $periodNumber,
            ]);
            return $session->fresh('records');
        });
    }

    public function correctRecord(AttendanceRecord $record, string $newStatus, User $actor, ?string $reason = null): AttendanceRecord
    {
        if ($record->status === $newStatus) return $record;
        return DB::transaction(function () use ($record, $newStatus, $actor, $reason) {
            AttendanceCorrection::create([
                'school_id' => $record->school_id, 'attendance_record_id' => $record->id,
                'old_status' => $record->status, 'new_status' => $newStatus,
                'corrected_by' => $actor->id, 'reason' => $reason, 'created_at' => now(),
            ]);
            $oldStatus = $record->status;
            $record->update(['status' => $newStatus]);
            if ($newStatus === 'absent' && $oldStatus !== 'absent') event(new StudentMarkedAbsent($record));
            $this->audit->logSchool($actor->id, 'attendance.corrected', AttendanceRecord::class, $record->id, ['status' => $oldStatus], ['status' => $newStatus]);
            return $record->fresh();
        });
    }
}
