<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Fee Structure</h1>
    <form wire:submit="save" class="space-y-6">
        <div><label class="block text-sm font-medium text-slate-700">Name *</label><input wire:model="name" type="text" placeholder="e.g. Grade 1-5 Standard Fees" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Applicability (leave blank for school-wide)</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700">Class</label><select wire:model="classId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Any class</option>@foreach ($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
                <div><label class="block text-sm font-medium text-slate-700">Student Category</label><input wire:model="studentCategory" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            </div>
            <div class="flex gap-6 mt-3">
                <label class="flex items-center gap-2 text-sm text-slate-600"><input wire:model="requiresTransport" type="checkbox" class="rounded border-slate-300"> Only Transport students</label>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input wire:model="requiresHostel" type="checkbox" class="rounded border-slate-300"> Only Hostel students</label>
            </div>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Fee Components (Annual Amount)</h2>
            <div class="space-y-2">@foreach ($componentAmounts as $i => $row)<div class="flex items-center gap-3"><span class="flex-1 text-sm text-slate-700">{{ $row['name'] }}</span><input wire:model="componentAmounts.{{ $i }}.annual_amount" type="number" step="0.01" class="w-32 rounded-lg border-slate-300 text-sm"></div>@endforeach</div>
        </div>
        <div class="pt-4 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Installment Plan</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-slate-700">Frequency</label><select wire:model.live="installmentFrequency" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="annual">Annual (1)</option><option value="half_yearly">Half-Yearly (2)</option><option value="quarterly">Quarterly (4)</option><option value="monthly">Monthly (12)</option><option value="custom">Custom</option></select></div>
                @if ($installmentFrequency === 'custom')<div><label class="block text-sm font-medium text-slate-700">Number of Installments</label><input wire:model="installmentCount" type="number" min="1" max="12" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>@endif
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.fees.structures.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create Fee Structure</button></div>
    </form>
</div>
