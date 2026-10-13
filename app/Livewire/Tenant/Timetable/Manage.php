<?php
namespace App\Livewire\Tenant\Timetable;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Models\Timetable;
use App\Domain\Tenant\Models\TimetablePeriod;
use App\Domain\Tenant\Services\TimetableService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Manage extends Component
{
    public const DAYS = ['monday','tuesday','wednesday','thursday','friday','saturday'];
    public ?int $sectionId = null;
    public ?Timetable $timetable = null;
    public ?string $editingDay = null;
    public ?int $editingPeriodId = null;
    public ?int $cellSubjectId = null;
    public ?int $cellTeacherId = null;
    public ?int $cellRoomId = null;

    public function mount(): void { $this->authorize('viewAny', Timetable::class); }

    public function loadSection(): void
    {
        $this->validate(['sectionId' => ['required', 'exists:sections,id']]);
        $section = Section::with('schoolClass.academicSession')->findOrFail($this->sectionId);
        $this->timetable = Timetable::firstOrCreate(
            ['academic_session_id' => $section->schoolClass->academic_session_id, 'class_id' => $section->class_id, 'section_id' => $section->id],
            ['status' => 'draft']
        );
    }

    public function openCell(string $day, int $periodId): void
    {
        $this->authorize('update', $this->timetable);
        $existing = $this->timetable->entries()->where('day_of_week', $day)->where('timetable_period_id', $periodId)->first();
        $this->editingDay = $day;
        $this->editingPeriodId = $periodId;
        $this->cellSubjectId = $existing?->subject_id;
        $this->cellTeacherId = $existing?->teacher_id;
        $this->cellRoomId = $existing?->room_id;
    }

    public function saveCell(TimetableService $timetableService): void
    {
        $this->authorize('update', $this->timetable);
        $this->validate(['cellSubjectId' => ['required', 'exists:subjects,id'], 'cellTeacherId' => ['required', 'exists:teachers,id']]);
        try {
            $timetableService->setCell($this->timetable, $this->editingDay, $this->editingPeriodId, $this->cellSubjectId, $this->cellTeacherId, $this->cellRoomId, auth()->user());
            $this->closeCell();
            $this->timetable = $this->timetable->fresh('entries');
        } catch (RuntimeException $e) {
            $this->addError('cellTeacherId', $e->getMessage());
        }
    }

    public function closeCell(): void { $this->reset(['editingDay', 'editingPeriodId', 'cellSubjectId', 'cellTeacherId', 'cellRoomId']); }

    public function publish(TimetableService $timetableService): void
    {
        $this->authorize('publish', $this->timetable);
        try {
            $this->timetable = $timetableService->publish($this->timetable, auth()->user());
            session()->flash('success', 'Timetable published.');
        } catch (RuntimeException $e) {
            $this->addError('publish', $e->getMessage());
        }
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.timetable.manage', [
            'sections' => Section::whereHas('schoolClass', fn($q) => $q->where('school_id', $school->id))->with('schoolClass')->orderBy('class_id')->orderBy('name')->get(),
            'periods' => TimetablePeriod::where('school_id', $school->id)->where('is_teaching_period', true)->orderBy('sort_order')->get(),
            'teachers' => Teacher::with('user')->where('status', 'active')->get(),
            'subjects' => $this->timetable ? $this->timetable->schoolClass->subjects : collect(),
        ]);
    }
}
