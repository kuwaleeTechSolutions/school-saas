<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Homework</h1>@can('create', \App\Domain\Tenant\Models\Homework::class)<a href="{{ route('tenant.homework.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Assign Homework</a>@endcan</div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Title</th><th class="px-4 py-3 text-left font-medium text-slate-600">Class / Subject</th><th class="px-4 py-3 text-left font-medium text-slate-600">Due</th><th class="px-4 py-3 text-left font-medium text-slate-600">Submissions</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($homeworkList as $hw)
                    <tr wire:key="hw-{{ $hw->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $hw->title }}</td><td class="px-4 py-3 text-slate-600">{{ $hw->schoolClass->name }} - {{ $hw->section->name }} &middot; {{ $hw->subject->name }}</td><td class="px-4 py-3 text-slate-600">{{ $hw->due_date->format('d M Y') }}</td><td class="px-4 py-3 text-slate-600">{{ $hw->graded_count }} / {{ $hw->submissions_count }} graded</td><td class="px-4 py-3 text-right"><a href="{{ route('tenant.homework.show', $hw) }}" class="text-slate-500 hover:text-slate-800">View</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No homework assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $homeworkList->links() }}</div>
</div>
