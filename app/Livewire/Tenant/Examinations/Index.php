<?php
namespace App\Livewire\Tenant\Examinations;
use App\Domain\Tenant\Models\Exam;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public function mount(): void { $this->authorize('viewAny', Exam::class); }
    public function render()
    {
        $exams = Exam::query()->with(['examType', 'schoolClass'])->withCount('examSubjects')->orderByDesc('starts_on')->paginate(15);
        return view('livewire.tenant.examinations.index', ['exams' => $exams]);
    }
}
