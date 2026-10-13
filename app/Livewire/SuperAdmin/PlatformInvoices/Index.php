<?php
namespace App\Livewire\SuperAdmin\PlatformInvoices;
use App\Domain\Platform\Models\PlatformInvoice;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
#[Layout('components.layouts.super-admin')]
class Index extends Component
{
    use WithPagination;
    public string $statusFilter = '';
    public function mount(): void { $this->authorize('viewAny', PlatformInvoice::class); }
    public function render()
    {
        $invoices = PlatformInvoice::with('school')->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))->orderByDesc('due_date')->paginate(15);
        return view('livewire.super-admin.platform-invoices.index', ['invoices' => $invoices]);
    }
}
