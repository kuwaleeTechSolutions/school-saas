<?php
namespace App\Domain\Tenant\Reports\Definitions;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Reports\ReportDefinition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
/** Per-student counts over a date range (max 92 days). "Attended" = present + late + half_day, matching report cards. */
class AttendanceSummaryReport implements ReportDefinition
{
    public const MAX_DAYS = 92;
    public function key(): string { return 'attendance_summary'; }
    public function title(): string { return 'Attendance Summary'; }
    public function permission(): string { return 'attendance.view'; }
    public function filters(): array
    {
        return ['from' => ['label' => 'From', 'type' => 'date'], 'to' => ['label' => 'To', 'type' => 'date'], 'section_id' => ['label' => 'Section ID (optional)', 'type' => 'text']];
    }
    public function columns(): array { return ['admission_number' => 'Admission #', 'student' => 'Student', 'attended' => 'Attended', 'absent' => 'Absent', 'total' => 'Marked Days', 'percent' => 'Attendance %']; }
    public function rules(array $input = []): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from', function ($attr, $value, $fail) use ($input) {
                try { if (Carbon::parse($input['from'] ?? null)->diffInDays(Carbon::parse($value)) > self::MAX_DAYS) $fail('The date range may not exceed '.self::MAX_DAYS.' days.'); } catch (\Throwable) {}
            }],
            'section_id' => ['nullable', 'integer'],
        ];
    }
    public function rows(array $filters, int $limit): Collection
    {
        $records = AttendanceRecord::query()->with('student')
            ->whereHas('session', fn ($q) => $q->where('period_number', 0)->whereBetween('attendance_date', [$filters['from'], $filters['to']])
                ->when($filters['section_id'] ?? null, fn ($q2, $v) => $q2->where('section_id', $v)))
            ->limit(200000)->get();
        return $records->groupBy('student_id')->map(function ($group) {
            $attended = $group->whereIn('status', ['present', 'late', 'half_day'])->count();
            $total = $group->count(); $s = $group->first()->student;
            return ['admission_number' => $s->admission_number, 'student' => $s->fullName(), 'attended' => $attended, 'absent' => $group->where('status', 'absent')->count(), 'total' => $total, 'percent' => $total ? round($attended / $total * 100, 1) : 0.0];
        })->sortBy('student')->values()->take($limit);
    }
}
