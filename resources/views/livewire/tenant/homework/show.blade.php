<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">{{ $homework->title }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $homework->schoolClass->name }} - {{ $homework->section->name }} &middot; {{ $homework->subject->name }} &middot; Due {{ $homework->due_date->format('d M Y') }}</p>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-left font-medium text-slate-600">Grade</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($homework->submissions as $submission)
                    <tr wire:key="sub-{{ $submission->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $submission->student->fullName() }}</td>
                        <td class="px-4 py-3">{{ str($submission->status)->headline() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $submission->grade_points ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">@can('grade', $homework)<button wire:click="startGrading({{ $submission->id }})" class="text-teal-700 hover:text-teal-900">Grade</button>@endcan</td>
                    </tr>
                    @if ($gradingSubmissionId === $submission->id)
                        <tr><td colspan="4" class="px-4 py-3 bg-slate-50">
                            <div class="flex gap-3 items-start">
                                <div><label class="block text-xs font-medium text-slate-700">Grade Points</label><input wire:model="gradePoints" type="number" step="0.01" class="mt-1 w-24 rounded-lg border-slate-300 text-sm">@error('gradePoints') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror</div>
                                <div class="flex-1"><label class="block text-xs font-medium text-slate-700">Comment</label><input wire:model="teacherComment" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                                <button wire:click="saveGrade" class="mt-5 rounded-lg bg-teal-700 px-3 py-1.5 text-xs text-white">Save</button>
                            </div>
                        </td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
