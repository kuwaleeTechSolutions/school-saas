<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Examinations</h1>@can('create', \App\Domain\Tenant\Models\Exam::class)<a href="{{ route('tenant.examinations.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ New Exam</a>@endcan</div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Exam</th><th class="px-4 py-3 text-left font-medium text-slate-600">Type</th><th class="px-4 py-3 text-left font-medium text-slate-600">Class</th><th class="px-4 py-3 text-left font-medium text-slate-600">Subjects</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($exams as $exam)
                    <tr wire:key="exam-{{ $exam->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $exam->name }}</td><td class="px-4 py-3 text-slate-600">{{ $exam->examType->name }}</td><td class="px-4 py-3 text-slate-600">{{ $exam->schoolClass->name }}</td><td class="px-4 py-3 text-slate-600">{{ $exam->exam_subjects_count }}</td><td class="px-4 py-3 text-right"><a href="{{ route('tenant.results.marks-entry', ['exam' => $exam->id]) }}" class="text-slate-500 hover:text-slate-800">Manage Marks</a></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No exams scheduled yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $exams->links() }}</div>
</div>
