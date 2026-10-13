<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Take Attendance</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @error('save') <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Class / Section</label><select wire:model="sectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select>@error('sectionId') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-sm font-medium text-slate-700">Date</label><input wire:model="attendanceDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Period</label><select wire:model="periodNumber" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="0">Daily (whole day)</option>@for ($p = 1; $p <= 8; $p++)<option value="{{ $p }}">Period {{ $p }}</option>@endfor</select></div>
        </div>
        <div class="flex justify-end pt-4"><button wire:click="loadRoster" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Load Roster</button></div>
    </div>
    @if ($students->isNotEmpty())
        @if ($existingSessionId)<div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">Attendance already exists; saving again will record as corrections.</div>@endif
        @if ($isLocked)<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">This attendance session is locked.</div>@endif
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm mb-4">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Student</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($students as $enrollment)
                        <tr wire:key="att-{{ $enrollment->student_id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $enrollment->student->fullName() }}</td><td class="px-4 py-3"><select wire:model="statuses.{{ $enrollment->student_id }}" class="rounded-lg border-slate-300 text-sm" @disabled($isLocked)><option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option><option value="half_day">Half Day</option><option value="leave">Leave</option></select></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @unless ($isLocked)<div class="flex justify-end"><button wire:click="save" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Save Attendance</button></div>@endunless
    @endif
</div>
