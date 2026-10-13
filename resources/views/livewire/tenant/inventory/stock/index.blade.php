<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Stock Transactions</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Record Receipt / Issue</h2>
        <form wire:submit="record" class="grid grid-cols-4 gap-3 items-end">
            <div><label class="block text-xs font-medium text-slate-700">Item</label><select wire:model="itemId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select>@error('itemId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Type</label><select wire:model="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="receipt">Receipt (in)</option><option value="issue">Issue (out)</option></select></div>
            <div><label class="block text-xs font-medium text-slate-700">Quantity</label><input wire:model="quantity" type="number" min="1" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('quantity') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Reference</label><input wire:model="reference" type="text" placeholder="e.g. PO-2026-01" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div class="col-span-4"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Record</button></div>
        </form>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Recent Transactions</h2>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Item</th><th class="px-3 py-2 text-left font-medium text-slate-600">Type</th><th class="px-3 py-2 text-left font-medium text-slate-600">Quantity</th><th class="px-3 py-2 text-left font-medium text-slate-600">Reference</th><th class="px-3 py-2 text-left font-medium text-slate-600">Date</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTransactions as $txn)
                        <tr><td class="px-3 py-2 text-slate-700">{{ $txn->item->name }}</td><td class="px-3 py-2 text-slate-600">{{ ucfirst($txn->type) }}</td><td class="px-3 py-2 {{ $txn->quantity < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $txn->quantity }}</td><td class="px-3 py-2 text-slate-500">{{ $txn->reference ?? '—' }}</td><td class="px-3 py-2 text-slate-500">{{ $txn->transacted_at->format('d M Y') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
