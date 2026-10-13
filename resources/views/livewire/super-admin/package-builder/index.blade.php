<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Packages</h1><input wire:model.live.debounce.300ms="search" type="search" placeholder="Search packages&hellip;" class="rounded-lg border-slate-300 text-sm"></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Package</th><th class="px-4 py-3 text-left font-medium text-slate-600">Monthly Price</th><th class="px-4 py-3 text-left font-medium text-slate-600">Students Limit</th><th class="px-4 py-3 text-left font-medium text-slate-600">Modules</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($plans as $plan)
                    <tr wire:key="plan-{{ $plan->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $plan->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $this->monthlyPriceFor($plan) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $this->studentLimitFor($plan) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $plan->modules->count() }} modules</td>
                        <td class="px-4 py-3"><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', 'bg-emerald-100 text-emerald-700' => $plan->status === 'active', 'bg-slate-100 text-slate-600' => $plan->status !== 'active'])>{{ ucfirst($plan->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No packages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $plans->links() }}</div>
</div>
