<?php
namespace App\Livewire\Tenant\Inventory\Stock;
use App\Domain\Tenant\Models\InventoryItem;
use App\Domain\Tenant\Services\InventoryService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?int $itemId = null;
    public string $type = 'receipt';
    public int $quantity = 1;
    public string $reference = '';

    public function mount(): void { $this->authorize('viewAny', InventoryItem::class); }

    public function record(InventoryService $inventoryService): void
    {
        $this->authorize('update', InventoryItem::class);
        $this->validate(['itemId' => ['required', 'exists:inventory_items,id'], 'quantity' => ['required', 'integer', 'min:1']]);
        try {
            $item = InventoryItem::findOrFail($this->itemId);
            if ($this->type === 'receipt') { $inventoryService->receive($item, $this->quantity, $this->reference ?: null, auth()->user()); }
            else { $inventoryService->issue($item, $this->quantity, $this->reference ?: null, auth()->user()); }
            $this->reset(['quantity', 'reference']);
            session()->flash('success', 'Transaction recorded.');
        } catch (RuntimeException $e) {
            $this->addError('quantity', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.inventory.stock.index', [
            'items' => InventoryItem::orderBy('name')->get(),
            'recentTransactions' => \App\Domain\Tenant\Models\InventoryTransaction::with('item')->orderByDesc('transacted_at')->limit(20)->get(),
        ]);
    }
}
