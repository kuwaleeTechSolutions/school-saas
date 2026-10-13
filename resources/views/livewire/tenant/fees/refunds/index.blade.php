<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Fee Refunds</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    @can('request', \App\Domain\Tenant\Models\FeeRefund::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Request a Refund</h2>
            <form wire:submit="requestRefund" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700">Payment *</label><select wire:model="paymentId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select a payment&hellip;</option>@foreach ($payments as $payment)<option value="{{ $payment->id }}">{{ $payment->invoice->student->fullName() }} &mdash; {{ number_format($payment->amount, 2) }} ({{ $payment->paid_at->format('d M Y') }})</option>@endforeach</select>@error('paymentId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Refund Amount *</label><input wire:model="refundAmount" type="number" step="0.01" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('refundAmount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div></div>
                <div><label class="block text-sm font-medium text-slate-700">Reason *</label><textarea wire:model="refundReason" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></textarea>@error('refundReason') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                <div class="flex justify-end"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Request Refund</button></div>
            </form>
        </div>
    @endcan
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Amount</th><th class="px-4 py-3 text-left font-medium text-slate-600">Reason</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($refunds as $refund)
                    <tr wire:key="refund-{{ $refund->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $refund->payment->invoice->student->fullName() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($refund->amount, 2) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $refund->reason }}</td>
                        <td class="px-4 py-3">{{ str($refund->status)->headline() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if ($refund->status === 'requested')
                                @can('approve', $refund)<button wire:click="approve({{ $refund->id }})" wire:confirm="Approve?" class="text-emerald-700 hover:text-emerald-900">Approve</button>@endcan
                                @can('reject', $refund)<button wire:click="startReject({{ $refund->id }})" class="text-red-600 hover:text-red-800">Reject</button>@endcan
                            @endif
                        </td>
                    </tr>
                    @if ($rejectingRefundId === $refund->id)
                        <tr><td colspan="5" class="px-4 py-3 bg-slate-50"><div class="flex gap-3 items-start"><input wire:model="rejectionReason" type="text" placeholder="Rejection reason" class="flex-1 rounded-lg border-slate-300 text-sm"><button wire:click="confirmReject" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs text-white">Confirm Reject</button></div>@error('rejectionReason') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror</td></tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No refunds yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
