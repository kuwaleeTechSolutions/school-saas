<?php
namespace App\Livewire\Tenant\Fees\Invoices;
use App\Domain\Tenant\Models\FeeInvoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public string $statusFilter = '';
    public function mount(): void { $this->authorize('viewAny', FeeInvoice::class); }
    public function render()
    {
        $invoices = FeeInvoice::query()->with('student')
            ->when($this->search, fn($q) => $q->whereHas('student', fn($q2) => $q2->where('first_name', 'like', "%{$this->search}%")->orWhere('admission_number', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('due_date')->paginate(15);
        return view('livewire.tenant.fees.invoices.index', ['invoices' => $invoices]);
    }
}
