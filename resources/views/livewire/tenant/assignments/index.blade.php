<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Assignments</h1>@can('create', \App\Domain\Tenant\Models\Assignment::class)<a href="{{ route('tenant.assignments.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ New Assignment</a>@endcan</div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Title</th><th class="px-4 py-3 text-left font-medium text-slate-600">Class / Subject</th><th class="px-4 py-3 text-left font-medium text-slate-600">Max Marks</th><th class="px-4 py-3 text-left font-medium text-slate-600">Due</th><th class="px-4 py-3 text-left font-medium text-slate-600">Graded</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($assignmentsList as $a)
                    <tr wire:key="assignment-{{ $a->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $a->title }}</td><td class="px-4 py-3 text-slate-600">{{ $a->schoolClass->name }} - {{ $a->section->name }} &middot; {{ $a->subject->name }}</td><td class="px-4 py-3 text-slate-600">{{ $a->max_marks }}</td><td class="px-4 py-3 text-slate-600">{{ $a->due_date->format('d M Y') }}</td><td class="px-4 py-3 text-slate-600">{{ $a->graded_count }} / {{ $a->submissions_count }}</td><td class="px-4 py-3 text-right"><a href="{{ route('tenant.assignments.show', $a) }}" class="text-slate-500 hover:text-slate-800">View</a></td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $assignmentsList->links() }}</div>
</div>
