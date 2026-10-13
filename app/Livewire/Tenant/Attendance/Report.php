<?php
namespace App\Livewire\Tenant\Attendance;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\Section;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Report extends Component
{
    public ?int $sectionId = null;
    public string $fromDate;
    public string $toDate;

    public function mount(): void
    {
        $this->authorize('viewAny', AttendanceRecord::class);
        $this->fromDate = now()->subDays(7)->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $summary = collect();
        if ($this->sectionId) {
            $summary = AttendanceRecord::query()
                ->whereHas('session', fn($q) => $q->where('section_id', $this->sectionId)->whereBetween('attendance_date', [$this->fromDate, $this->toDate]))
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        }
        return view('livewire.tenant.attendance.report', [
            'sections' => Section::whereHas('schoolClass', fn($q) => $q->where('school_id', $school->id))->with('schoolClass')->orderBy('class_id')->orderBy('name')->get(),
            'summary' => $summary,
        ]);
    }
}
