<?php
namespace App\Livewire\Tenant\Attendance;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\AttendanceSession;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\StudentEnrollment;
use App\Domain\Tenant\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Take extends Component
{
    public ?int $sectionId = null;
    public string $attendanceDate;
    public int $periodNumber = 0;
    public array $statuses = [];
    public ?int $existingSessionId = null;
    public bool $isLocked = false;

    public function mount(): void
    {
        $this->authorize('create', AttendanceRecord::class);
        $this->attendanceDate = now()->format('Y-m-d');
    }

    public function loadRoster(): void
    {
        $this->validate(['sectionId' => ['required', 'exists:sections,id'], 'attendanceDate' => ['required', 'date'], 'periodNumber' => ['required', 'integer', 'min:0', 'max:12']]);
        $section = Section::findOrFail($this->sectionId);
        $existing = AttendanceSession::where('class_id', $section->class_id)->where('section_id', $section->id)
            ->where('attendance_date', $this->attendanceDate)->where('period_number', $this->periodNumber)->with('records')->first();
        $this->existingSessionId = $existing?->id;
        $this->isLocked = (bool) $existing?->locked_at;
        $enrollments = StudentEnrollment::where('section_id', $section->id)->where('status', 'active')->with('student')->get();
        $this->statuses = [];
        foreach ($enrollments as $enrollment) {
            $existingRecord = $existing?->records->firstWhere('student_id', $enrollment->student_id);
            $this->statuses[$enrollment->student_id] = $existingRecord?->status ?? 'present';
        }
    }

    public function save(AttendanceService $attendanceService): void
    {
        $this->authorize($this->existingSessionId ? 'correct' : 'create', AttendanceRecord::class);
        $section = Section::findOrFail($this->sectionId);
        try {
            $attendanceService->takeAttendance($section, Carbon::parse($this->attendanceDate), $this->periodNumber, $this->statuses, auth()->user());
            session()->flash('success', 'Attendance saved.');
            $this->loadRoster();
        } catch (RuntimeException $e) {
            $this->addError('save', $e->getMessage());
        }
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.attendance.take', [
            'sections' => Section::whereHas('schoolClass', fn($q) => $q->where('school_id', $school->id))->with('schoolClass')->orderBy('class_id')->orderBy('name')->get(),
            'students' => $this->sectionId ? StudentEnrollment::where('section_id', $this->sectionId)->where('status', 'active')->with('student')->get() : collect(),
        ]);
    }
}
