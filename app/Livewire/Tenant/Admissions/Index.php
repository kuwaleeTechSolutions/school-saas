<?php
namespace App\Livewire\Tenant\Admissions;
use App\Domain\Tenant\Models\AdmissionApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public string $stageFilter = '';
    public function mount(): void { $this->authorize('viewAny', AdmissionApplication::class); }
    public function render()
    {
        $applications = AdmissionApplication::query()->with('academicSession')
            ->when($this->search, fn($q) => $q->where(function ($q2) {
                $q2->where('applicant_first_name', 'like', "%{$this->search}%")->orWhere('applicant_last_name', 'like', "%{$this->search}%")->orWhere('guardian_name', 'like', "%{$this->search}%");
            }))
            ->when($this->stageFilter, fn($q) => $q->where('stage', $this->stageFilter))
            ->orderByDesc('created_at')->paginate(15);
        return view('livewire.tenant.admissions.index', ['applications' => $applications]);
    }
}
