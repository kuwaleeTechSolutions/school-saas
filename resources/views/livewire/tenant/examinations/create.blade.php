<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">New Exam</h1>
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Exam Type *</label><select wire:model="examTypeId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($examTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select>@error('examTypeId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Class *</label><select wire:model="classId" wire:change="loadSubjectsForClass" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select>@error('classId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div class="col-span-2"><label class="block text-sm font-medium text-slate-700">Exam Name *</label><input wire:model="name" type="text" placeholder="e.g. Mid-Term 2026" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Starts On</label><input wire:model="startsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Ends On</label><input wire:model="endsOn" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('endsOn') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        </div>
        @if (!empty($subjectRows))
            <div class="pt-4 border-t border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700 mb-3">Subjects &amp; Marks</h2>
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Subject</th><th class="px-3 py-2 text-left font-medium text-slate-600">Max Marks</th><th class="px-3 py-2 text-left font-medium text-slate-600">Pass Marks</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">@foreach ($subjectRows as $i => $row)<tr><td class="px-3 py-2 font-medium text-slate-800">{{ $row['subject_name'] }}</td><td class="px-3 py-2"><input wire:model="subjectRows.{{ $i }}.max_marks" type="number" class="w-24 rounded border-slate-300 text-sm"></td><td class="px-3 py-2"><input wire:model="subjectRows.{{ $i }}.pass_marks" type="number" class="w-24 rounded border-slate-300 text-sm"></td></tr>@endforeach</tbody>
                    </table>
                </div>
            </div>
        @endif
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.examinations.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Create Exam</button></div>
    </form>
</div>
