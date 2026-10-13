<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Fee Structures</h1>@can('create', \App\Domain\Tenant\Models\FeeStructure::class)<a href="{{ route('tenant.fees.structures.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ New Fee Structure</a>@endcan</div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Applies To</th><th class="px-4 py-3 text-left font-medium text-slate-600">Annual Total</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($structures as $structure)
                    <tr wire:key="structure-{{ $structure->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $structure->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $structure->schoolClass?->name ?? 'All classes' }}@if ($structure->section) - {{ $structure->section->name }}@endif @if ($structure->requires_transport)<span class="text-xs text-blue-600">(Transport)</span>@endif @if ($structure->requires_hostel)<span class="text-xs text-purple-600">(Hostel)</span>@endif</td>
                        <td class="px-4 py-3 text-slate-600">{{ number_format($structure->totalAnnualAmount(), 2) }}</td>
                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $structure->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $structure->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No fee structures yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
