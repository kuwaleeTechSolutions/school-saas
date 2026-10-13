<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Platform Invoices</h1>
    <div class="mb-4"><select wire:model.live="statusFilter" class="rounded-lg border-slate-300 text-sm"><option value="">All statuses</option><option value="unpaid">Unpaid</option><option value="partially_paid">Partially Paid</option><option value="paid">Paid</option><option value="overdue">Overdue</option><option value="cancelled">Cancelled</option></select></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Invoice #</th><th class="px-4 py-3 text-left font-medium text-slate-600">School</th><th class="px-4 py-3 text-left font-medium text-slate-600">Due</th><th class="px-4 py-3 text-left font-medium text-slate-600">Total</th><th class="px-4 py-3 text-left font-medium text-slate-600">Balance</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($invoices as $invoice)
            <tr wire:key="pinv-{{ $invoice->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $invoice->invoice_number }}</td><td class="px-4 py-3 text-slate-600">{{ $invoice->school->name }}</td><td class="px-4 py-3 text-slate-600">{{ $invoice->due_date->format('d M Y') }}</td><td class="px-4 py-3 text-slate-600">{{ number_format($invoice->total_amount, 2) }}</td><td class="px-4 py-3 text-slate-600">{{ number_format($invoice->balance_amount, 2) }}</td><td class="px-4 py-3">{{ str($invoice->status)->headline() }}</td><td class="px-4 py-3 text-right"><a href="{{ route('super-admin.platform-invoices.show', $invoice) }}" class="text-slate-500 hover:text-slate-800">View</a></td></tr>
        @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">No platform invoices found.</td></tr>
        @endforelse
        </tbody></table></div>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
