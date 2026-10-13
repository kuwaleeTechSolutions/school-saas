<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Parents / Guardians</h1>@can('create', \App\Domain\Tenant\Models\Guardian::class)<a href="{{ route('tenant.guardians.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Add Guardian</a>@endcan</div>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search guardians&hellip;" class="mb-4 w-full rounded-lg border-slate-300 text-sm">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Phone</th><th class="px-4 py-3 text-left font-medium text-slate-600">Children</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($guardians as $guardian)
                    <tr wire:key="guardian-{{ $guardian->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $guardian->name }}</td><td class="px-4 py-3 text-slate-600">{{ $guardian->phone ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ $guardian->students_count }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No guardians found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $guardians->links() }}</div>
</div>
