<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Attendance Report</h1>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="grid grid-cols-3 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Class / Section</label><select wire:model.live="sectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700">From</label><input wire:model.live="fromDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">To</label><input wire:model.live="toDate" type="date" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
    </div>
    @if ($sectionId)
        <div class="grid grid-cols-5 gap-4">@foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'half_day' => 'Half Day', 'leave' => 'Leave'] as $key => $label)<div class="rounded-xl border border-slate-200 bg-white p-4 text-center"><div class="text-xs uppercase text-slate-500">{{ $label }}</div><div class="mt-1 text-xl font-semibold text-slate-800">{{ $summary[$key] ?? 0 }}</div></div>@endforeach</div>
    @else
        <p class="text-sm text-slate-500">Select a class/section to view its attendance summary.</p>
    @endif
</div>
