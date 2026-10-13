<?php
namespace App\Livewire\Tenant\Results;
use App\Domain\Tenant\Models\ExamSubject;
use App\Domain\Tenant\Services\MarksWorkflowService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class ApprovalQueue extends Component
{
    public string $statusFilter = 'submitted';
    public function mount(): void { $this->authorize('viewAny', \App\Domain\Tenant\Models\Exam::class); }

    public function advance(int $examSubjectId, string $toStatus, MarksWorkflowService $workflow): void
    {
        $examSubject = ExamSubject::findOrFail($examSubjectId);
        $ability = $toStatus === 'published' ? 'publish' : 'verify';
        $this->authorize($ability, $examSubject);
        try {
            $workflow->transition($examSubject, $toStatus, auth()->user());
            session()->flash('success', "Marks moved to '{$toStatus}'.");
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $examSubjects = ExamSubject::query()->with(['exam.schoolClass', 'subject'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))->orderByDesc('updated_at')->get();
        return view('livewire.tenant.results.approval-queue', ['examSubjects' => $examSubjects]);
    }
}
