<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Teachers</h1>@can('create', \App\Domain\Tenant\Models\Teacher::class)<a href="{{ route('tenant.teachers.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Add Teacher</a>@endcan</div>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search teachers&hellip;" class="mb-4 w-full rounded-lg border-slate-300 text-sm">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Name</th><th class="px-4 py-3 text-left font-medium text-slate-600">Email</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($teachers as $teacher)
                    <tr wire:key="teacher-{{ $teacher->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $teacher->user->name }}</td><td class="px-4 py-3 text-slate-600">{{ $teacher->user->email }}</td><td class="px-4 py-3">{{ str($teacher->status)->headline() }}</td><td class="px-4 py-3 text-right">@can('update', $teacher)<a href="{{ route('tenant.teachers.edit', $teacher) }}" class="text-slate-500 hover:text-slate-800">Edit</a>@endcan</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No teachers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $teachers->links() }}</div>
</div>
