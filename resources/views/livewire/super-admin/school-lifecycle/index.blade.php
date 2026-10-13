<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Schools</h1><input wire:model.live.debounce.300ms="search" type="search" placeholder="Search schools&hellip;" class="rounded-lg border-slate-300 text-sm"></div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">School</th><th class="px-4 py-3 text-left font-medium text-slate-600">Plan</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($schools as $school)
                    <tr wire:key="school-{{ $school->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $school->name }}<div class="text-xs text-slate-500">{{ $school->slug }}</div></td>
                        <td class="px-4 py-3 text-slate-600">{{ $school->activeSubscription?->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ str($school->status)->headline() }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($school->status === 'suspended')
                                <button wire:click="activate({{ $school->id }})" wire:confirm="Reactivate?" class="text-emerald-600 hover:text-emerald-800">Activate</button>
                            @else
                                <button wire:click="suspend({{ $school->id }})" wire:confirm="Suspend?" class="text-red-600 hover:text-red-800">Suspend</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No schools found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $schools->links() }}</div>
</div>
