<?php
namespace App\Livewire\Tenant\Guardian;
use App\Domain\Tenant\Models\Guardian;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public function mount(): void { $this->authorize('viewAny', Guardian::class); }
    public function render()
    {
        $guardians = Guardian::query()->withCount('students')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')->paginate(15);
        return view('livewire.tenant.guardian.index', ['guardians' => $guardians]);
    }
}
