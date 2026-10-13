<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Inventory Items</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\InventoryItem::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Add Item</h2>
            <form wire:submit="addItem" class="grid grid-cols-4 gap-3">
                <input wire:model="name" type="text" placeholder="Item name" class="rounded-lg border-slate-300 text-sm">
                <select wire:model="category" class="rounded-lg border-slate-300 text-sm"><option value="consumable">Consumable</option><option value="asset">Asset</option></select>
                <input wire:model="unit" type="text" placeholder="Unit (pcs, box...)" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="reorderLevel" type="number" placeholder="Reorder level" class="rounded-lg border-slate-300 text-sm">
                <button type="submit" class="col-span-4 rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Add Item</button>
            </form>
            @error('name') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
        </div>
    @endcan
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Item</th><th class="px-4 py-3 text-left font-medium text-slate-600">Category</th><th class="px-4 py-3 text-left font-medium text-slate-600">Current Stock</th><th class="px-4 py-3 text-left font-medium text-slate-600">Reorder Level</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $row)
                    <tr wire:key="item-{{ $row['item']->id }}" class="{{ $row['is_low'] ? 'bg-amber-50' : '' }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $row['item']->name }}</td><td class="px-4 py-3 text-slate-600">{{ ucfirst($row['item']->category) }}</td><td class="px-4 py-3 text-slate-600">{{ $row['current_stock'] }} {{ $row['item']->unit }} {{ $row['is_low'] ? '(Low!)' : '' }}</td><td class="px-4 py-3 text-slate-600">{{ $row['item']->reorder_level }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
