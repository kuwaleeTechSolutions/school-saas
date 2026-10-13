<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Timetable;
use App\Domain\Tenant\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimetableService
{
    public function __construct(protected AuditService $audit) {}

    public function assertNoConflict(Timetable $timetable, string $dayOfWeek, int $periodId, int $teacherId, ?int $roomId, ?int $ignoreEntryId = null): void
    {
        $teacherConflict = TimetableEntry::where('day_of_week', $dayOfWeek)->where('timetable_period_id', $periodId)
            ->where('teacher_id', $teacherId)->where('timetable_id', '!=', $timetable->id)
            ->when($ignoreEntryId, fn($q) => $q->where('id', '!=', $ignoreEntryId))
            ->with('timetable.schoolClass', 'timetable.section')->first();
        if ($teacherConflict) {
            $other = $teacherConflict->timetable;
            throw new RuntimeException("This teacher is already scheduled for {$other->schoolClass->name} - {$other->section->name} at this day/period.");
        }
        if ($roomId) {
            $roomConflict = TimetableEntry::where('day_of_week', $dayOfWeek)->where('timetable_period_id', $periodId)
                ->where('room_id', $roomId)->where('timetable_id', '!=', $timetable->id)
                ->when($ignoreEntryId, fn($q) => $q->where('id', '!=', $ignoreEntryId))
                ->with('timetable.schoolClass', 'timetable.section')->first();
            if ($roomConflict) {
                $other = $roomConflict->timetable;
                throw new RuntimeException("This room is already booked for {$other->schoolClass->name} - {$other->section->name} at this day/period.");
            }
        }
    }

    public function setCell(Timetable $timetable, string $dayOfWeek, int $periodId, int $subjectId, int $teacherId, ?int $roomId, User $actor): TimetableEntry
    {
        return DB::transaction(function () use ($timetable, $dayOfWeek, $periodId, $subjectId, $teacherId, $roomId, $actor) {
            $existing = TimetableEntry::where('timetable_id', $timetable->id)->where('day_of_week', $dayOfWeek)->where('timetable_period_id', $periodId)->first();
            $this->assertNoConflict($timetable, $dayOfWeek, $periodId, $teacherId, $roomId, $existing?->id);
            $entry = TimetableEntry::updateOrCreate(
                ['timetable_id' => $timetable->id, 'day_of_week' => $dayOfWeek, 'timetable_period_id' => $periodId],
                ['subject_id' => $subjectId, 'teacher_id' => $teacherId, 'room_id' => $roomId]
            );
            $this->audit->logSchool($actor->id, 'timetable.cell_set', TimetableEntry::class, $entry->id, null, ['day' => $dayOfWeek, 'period_id' => $periodId, 'teacher_id' => $teacherId]);
            return $entry;
        });
    }

    public function publish(Timetable $timetable, User $actor): Timetable
    {
        if ($timetable->entries()->count() === 0) {
            throw new RuntimeException('Cannot publish an empty timetable. Add at least one cell first.');
        }
        $timetable->update(['status' => 'published', 'published_by' => $actor->id, 'published_at' => now()]);
        $this->audit->logSchool($actor->id, 'timetable.published', Timetable::class, $timetable->id);
        return $timetable->fresh();
    }
}
