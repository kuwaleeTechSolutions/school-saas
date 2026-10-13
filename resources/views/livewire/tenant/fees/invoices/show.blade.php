<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">{{ $invoice->invoice_number }}</h1><p class="text-sm text-slate-500">{{ $invoice->student->fullName() }} &middot; Due {{ $invoice->due_date->format('d M Y') }}</p></div>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-slate-100 text-slate-700">{{ str($invoice->status)->headline() }}</span>
    </div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm mb-6">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Description</th><th class="px-4 py-3 text-right font-medium text-slate-600">Amount</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($invoice->lineItems as $item)
                    <tr><td class="px-4 py-3 text-slate-700">{{ $item->description }}</td><td class="px-4 py-3 text-right {{ $item->amount < 0 ? 'text-emerald-700' : 'text-slate-700' }}">{{ number_format($item->amount, 2) }}</td></tr>
                @endforeach
                <tr class="font-semibold"><td class="px-4 py-3">Total</td><td class="px-4 py-3 text-right">{{ number_format($invoice->total_amount, 2) }}</td></tr>
                <tr><td class="px-4 py-3">Paid</td><td class="px-4 py-3 text-right text-emerald-700">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                <tr class="font-semibold"><td class="px-4 py-3">Balance</td><td class="px-4 py-3 text-right">{{ number_format($invoice->balance_amount, 2) }}</td></tr>
            </tbody>
        </table>
    </div>

    @if ($invoice->isOverdue())
        @can('generate', \App\Domain\Tenant\Models\FeeInvoice::class)
            <div class="mb-6"><button wire:click="refreshLateFee" class="rounded-lg border border-amber-300 px-4 py-2 text-sm text-amber-700">Recalculate Late Fee</button></div>
        @endcan
    @endif

    @if ($invoice->balance_amount > 0 && $invoice->status !== 'cancelled')
        @can('create', \App\Domain\Tenant\Models\FeePayment::class)
            <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6">
                @if (! $showPaymentForm)
                    <button wire:click="$set('showPaymentForm', true)" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Record Payment</button>
                @else
                    <h2 class="text-sm font-semibold text-slate-700 mb-3">Record Payment</h2>
                    <form wire:submit="recordPayment" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-slate-700">Amount *</label><input wire:model="paymentAmount" type="number" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('paymentAmount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                            <div><label class="block text-sm font-medium text-slate-700">Method *</label><select wire:model="paymentMethod" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="upi">UPI</option><option value="cheque">Cheque</option><option value="gateway">Payment Gateway</option></select></div>
                            <div class="col-span-2"><label class="block text-sm font-medium text-slate-700">Transaction Reference</label><input wire:model="transactionReference" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                        </div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="$set('showPaymentForm', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</button><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Payment</button></div>
                    </form>
                @endif
            </div>
        @endcan
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Payment History</h2>
        @forelse ($invoice->payments as $payment)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                <div><span class="font-medium text-slate-800">{{ number_format($payment->amount, 2) }}</span> <span class="text-slate-500">&middot; {{ str($payment->method)->headline() }} &middot; {{ $payment->paid_at->format('d M Y') }}</span></div>
                @if ($payment->receipt && $payment->receipt->pdf_status === 'generated')<a href="{{ route('tenant.fees.receipts.download', $payment->receipt) }}" class="text-teal-700 hover:underline">Download Receipt</a>@else<span class="text-slate-400 text-xs">Receipt {{ $payment->receipt?->pdf_status ?? 'pending' }}</span>@endif
            </div>
        @empty
            <p class="text-sm text-slate-500">No payments recorded yet.</p>
        @endforelse
    </div>
</div>
