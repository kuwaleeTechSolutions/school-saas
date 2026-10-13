<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Payroll</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\PayrollRun::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Generate Payroll</h2>
            <form wire:submit="generate" class="flex gap-3 items-end">
                <div><label class="block text-xs font-medium text-slate-700">Year</label><input wire:model="year" type="number" class="mt-1 w-28 rounded-lg border-slate-300 text-sm"></div>
                <div><label class="block text-xs font-medium text-slate-700">Month</label><input wire:model="month" type="number" min="1" max="12" class="mt-1 w-20 rounded-lg border-slate-300 text-sm"></div>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Generate</button>
            </form>
            @error('month') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
        </div>
    @endcan
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Period</th><th class="px-4 py-3 text-left font-medium text-slate-600">Payslips</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($runs as $run)
                    <tr wire:key="run-{{ $run->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $run->label() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $run->payslips_count }}</td>
                        <td class="px-4 py-3">{{ str($run->status)->headline() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('tenant.hr.payroll.show', $run) }}" class="text-slate-500 hover:text-slate-800">View</a>
                            @if ($run->status === 'draft')@can('finalize', $run)<button wire:click="finalize({{ $run->id }})" wire:confirm="Finalize this payroll run? Payslips will be generated for every staff member." class="text-teal-700 hover:underline">Finalize</button>@endcan @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No payroll runs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
