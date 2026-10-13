<?php
namespace App\Livewire\Tenant\Homework;
use App\Domain\Tenant\Models\Homework;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public function mount(): void { $this->authorize('viewAny', Homework::class); }
    public function render()
    {
        $homework = Homework::query()->with(['schoolClass', 'section', 'subject'])
            ->withCount(['submissions', 'submissions as graded_count' => fn($q) => $q->where('status', 'graded')])
            ->orderByDesc('due_date')->paginate(15);
        return view('livewire.tenant.homework.index', ['homeworkList' => $homework]);
    }
}
