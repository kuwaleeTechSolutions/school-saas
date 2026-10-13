<?php
namespace App\Livewire\Tenant\Assignments;
use App\Domain\Tenant\Models\Assignment;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public function mount(): void { $this->authorize('viewAny', Assignment::class); }
    public function render()
    {
        $assignments = Assignment::query()->with(['schoolClass', 'section', 'subject'])
            ->withCount(['submissions', 'submissions as graded_count' => fn($q) => $q->where('status', 'graded')])
            ->orderByDesc('due_date')->paginate(15);
        return view('livewire.tenant.assignments.index', ['assignmentsList' => $assignments]);
    }
}
