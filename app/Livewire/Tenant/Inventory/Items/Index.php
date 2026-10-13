<?php
namespace App\Livewire\Tenant\Inventory\Items;
use App\Domain\Tenant\Models\InventoryItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $name = '';
    public string $category = 'consumable';
    public string $unit = 'pcs';
    public int $reorderLevel = 0;

    public function mount(): void { $this->authorize('viewAny', InventoryItem::class); }

    public function addItem(): void
    {
        $this->authorize('create', InventoryItem::class);
        $this->validate(['name' => ['required', 'string', 'max:255']]);
        InventoryItem::create(['name' => $this->name, 'category' => $this->category, 'unit' => $this->unit, 'reorder_level' => $this->reorderLevel]);
        $this->reset(['name', 'reorderLevel']);
        session()->flash('success', 'Item added.');
    }

    public function render()
    {
        $items = InventoryItem::all()->map(fn($item) => ['item' => $item, 'current_stock' => $item->currentStock(), 'is_low' => $item->isBelowReorderLevel()]);
        return view('livewire.tenant.inventory.items.index', ['items' => $items]);
    }
}
