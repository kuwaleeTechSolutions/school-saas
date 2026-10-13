<div class="max-w-4xl mx-auto">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Student Promotion</h1>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @if ($step === 1)
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Step 1 &middot; Select Source and Target</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">From Class / Section</label>
                    <select wire:model="fromSectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select>
                    @error('fromSectionId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">To Academic Session</label>
                    <select wire:model="toAcademicSessionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($targetSessions as $session)<option value="{{ $session->id }}">{{ $session->name }}{{ $session->is_current ? ' (current)' : '' }}</option>@endforeach</select>
                    @error('toAcademicSessionId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end pt-2"><button wire:click="loadStudents" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Load Students</button></div>
            </div>
        @elseif ($step === 2)
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Step 2 &middot; Decisions ({{ count($rows) }} students)</h2>
            <div class="overflow-auto max-h-96 rounded-lg border border-slate-200 mb-4">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 sticky top-0"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Student</th><th class="px-3 py-2 text-left font-medium text-slate-600">Decision</th><th class="px-3 py-2 text-left font-medium text-slate-600">Target Class</th><th class="px-3 py-2 text-left font-medium text-slate-600">Target Section</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rows as $i => $row)
                            <tr wire:key="row-{{ $row['student_id'] }}">
                                <td class="px-3 py-2 font-medium text-slate-800">{{ $row['name'] }}</td>
                                <td class="px-3 py-2"><select wire:model="rows.{{ $i }}.decision" class="rounded-lg border-slate-300 text-xs"><option value="promoted">Promoted</option><option value="retained">Retained</option><option value="graduated">Graduated</option><option value="withdrawn">Withdrawn</option></select></td>
                                <td class="px-3 py-2">
                                    @if (in_array($row['decision'], ['promoted', 'retained']))
                                        <select wire:model="rows.{{ $i }}.to_class_id" class="rounded-lg border-slate-300 text-xs"><option value="">Select&hellip;</option>@foreach ($targetClasses as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select>
                                        @error("rows.{$i}.to_class_id") <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                                    @else
                                        <span class="text-slate-400 text-xs">N/A</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if (in_array($row['decision'], ['promoted', 'retained']))
                                        <select wire:model="rows.{{ $i }}.to_section_id" class="rounded-lg border-slate-300 text-xs"><option value="">Select&hellip;</option>@foreach ($this->sectionsForTargetClass($i) as $sec)<option value="{{ $sec->id }}">{{ $sec->name }}</option>@endforeach</select>
                                    @else
                                        <span class="text-slate-400 text-xs">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between pt-2"><button wire:click="restart" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Start Over</button><button wire:click="goToPreview" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Preview</button></div>
        @elseif ($step === 3)
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Step 3 &middot; Preview</h2>
            <div class="overflow-auto max-h-96 rounded-lg border border-slate-200 mb-4">
                <table class="min-w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Student</th><th class="px-3 py-2 text-left font-medium text-slate-600">Decision</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($rows as $row)<tr><td class="px-3 py-2 font-medium text-slate-800">{{ $row['name'] }}</td><td class="px-3 py-2 text-slate-600">{{ str($row['decision'])->headline() }}</td></tr>@endforeach</tbody></table>
            </div>
            @error('confirm') <p class="text-red-600 text-sm mb-3">{{ $message }}</p> @enderror
            <div class="flex justify-between pt-2"><button wire:click="$set('step', 2)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Back</button><button wire:click="confirm" wire:confirm="Confirm promotion?" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Confirm Promotion</button></div>
        @elseif ($step === 4)
            <div class="text-center py-8"><h2 class="text-lg font-semibold text-slate-800">Promotion complete</h2><p class="text-sm text-slate-500 mt-2">{{ count($rows) }} students processed in batch {{ $lastBatchUlid }}.</p><button wire:click="restart" class="mt-6 rounded-lg bg-teal-700 px-5 py-2.5 text-sm text-white">Run Another Promotion</button></div>
        @endif
    </div>
</div>
