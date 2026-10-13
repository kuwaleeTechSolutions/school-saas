<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">{{ $exam->name }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $exam->schoolClass->name }} &middot; Marks Entry</p>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @error('entries') <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="flex gap-3 items-end">
            <div class="flex-1"><label class="block text-sm font-medium text-slate-700">Subject</label><select wire:model="examSubjectId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($exam->examSubjects as $es)<option value="{{ $es->id }}">{{ $es->subject->name }} &mdash; {{ str($es->status)->headline() }}</option>@endforeach</select>@error('examSubjectId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <button wire:click="loadSubject" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Load</button>
        </div>
    </div>
    @if (!empty($entries))
        @php $currentSubject = $exam->examSubjects->firstWhere('id', $examSubjectId); @endphp
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm mb-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Marks (/ {{ $currentSubject?->max_marks }})</th><th class="px-4 py-3 text-left font-medium text-slate-600">Absent</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($entries as $i => $entry)
                        <tr wire:key="entry-{{ $entry['student_id'] }}">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $entry['student_name'] }}</td>
                            <td class="px-4 py-3"><input wire:model="entries.{{ $i }}.marks_obtained" type="number" step="0.01" @disabled($entry['is_absent']) class="w-24 rounded-lg border-slate-300 text-sm">@error("entries.{$i}.marks_obtained") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror</td>
                            <td class="px-4 py-3"><input wire:model="entries.{{ $i }}.is_absent" type="checkbox" class="rounded border-slate-300"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex justify-between">
            <button wire:click="saveEntries" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Save Draft</button>
            @can('enterMarks', $currentSubject)@if ($currentSubject?->status === 'entered')<button wire:click="submitForApproval" wire:confirm="Submit for approval?" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Submit for Approval</button>@endif@endcan
        </div>
    @endif
</div>
