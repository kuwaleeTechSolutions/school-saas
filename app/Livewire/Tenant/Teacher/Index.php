<?php
namespace App\Livewire\Tenant\Teacher;
use App\Domain\Tenant\Models\Teacher;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public function mount(): void { $this->authorize('viewAny', Teacher::class); }
    public function render()
    {
        $teachers = Teacher::query()->with('user')
            ->when($this->search, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->orderBy('id')->paginate(15);
        return view('livewire.tenant.teacher.index', ['teachers' => $teachers]);
    }
}
