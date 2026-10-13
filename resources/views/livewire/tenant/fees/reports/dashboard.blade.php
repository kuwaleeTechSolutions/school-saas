<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Financial Dashboard</h1>
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm mb-6">
        <div class="flex gap-3 items-end">
            <div><label class="block text-xs font-medium text-slate-700">From</label><input wire:model.live="fromDate" type="date" class="mt-1 rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-xs font-medium text-slate-700">To</label><input wire:model.live="toDate" type="date" class="mt-1 rounded-lg border-slate-300 text-sm"></div>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Collections (Period)</div><div class="mt-1 text-2xl font-semibold text-emerald-700">{{ number_format($collections, 2) }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Total Outstanding</div><div class="mt-1 text-2xl font-semibold text-amber-700">{{ number_format($outstandingTotal, 2) }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Overdue Invoices</div><div class="mt-1 text-2xl font-semibold text-red-700">{{ $overdueCount }}</div></div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 mb-8">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Collections by Method</h2>
        @if (empty($collectionsByMethod))<p class="text-sm text-slate-500">No payments in this period.</p>@else
            <div class="grid grid-cols-5 gap-3">@foreach ($collectionsByMethod as $method => $amount)<div class="rounded-lg bg-slate-50 p-3 text-center"><div class="text-xs text-slate-500">{{ str($method)->headline() }}</div><div class="text-sm font-semibold text-slate-800">{{ number_format($amount, 2) }}</div></div>@endforeach</div>
        @endif
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between mb-3"><h2 class="text-sm font-semibold text-slate-700">Top Outstanding Invoices</h2></div>
        @forelse ($outstandingInvoices as $invoice)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm"><span>{{ $invoice->student->fullName() }} &middot; {{ $invoice->invoice_number }}</span><span class="font-medium text-slate-800">{{ number_format($invoice->balance_amount, 2) }}</span></div>
        @empty
            <p class="text-sm text-slate-500">Nothing outstanding right now.</p>
        @endforelse
    </div>
</div>
