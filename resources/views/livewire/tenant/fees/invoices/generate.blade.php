<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Generate Invoices</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex gap-3 items-end">
            <div class="flex-1"><label class="block text-sm font-medium text-slate-700">Installment</label><select wire:model="feeInstallmentId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($installments as $installment)<option value="{{ $installment->id }}">{{ $installment->plan->feeStructure->name }} &mdash; {{ $installment->label }} (due {{ $installment->due_date->format('d M Y') }})</option>@endforeach</select>@error('feeInstallmentId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <button wire:click="generate" wire:confirm="Generate invoices?" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Generate</button>
        </div>
    </div>
</div>
