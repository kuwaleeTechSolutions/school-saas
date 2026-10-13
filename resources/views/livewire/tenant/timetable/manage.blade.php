<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Timetable</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @error('publish') <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
        <div class="flex gap-3 items-end">
            <div class="flex-1"><label class="block text-sm font-medium text-slate-700">Class / Section</label><select wire:model="sectionId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->schoolClass->name }} - {{ $section->name }}</option>@endforeach</select></div>
            <button wire:click="loadSection" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Load</button>
        </div>
    </div>
    @if ($timetable)
        <div class="flex items-center justify-between mb-3">
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $timetable->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ str($timetable->status)->headline() }}</span>
            @can('publish', $timetable)@if ($timetable->status !== 'published')<button wire:click="publish" wire:confirm="Publish?" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Publish</button>@endif@endcan
        </div>
        <div class="overflow-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Period</th>@foreach (\App\Livewire\Tenant\Timetable\Manage::DAYS as $day)<th class="px-3 py-2 text-left font-medium text-slate-600">{{ ucfirst($day) }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($periods as $period)
                        <tr>
                            <td class="px-3 py-2 font-medium text-slate-700 whitespace-nowrap">{{ $period->name }}<div class="text-xs text-slate-400">{{ $period->starts_at }}-{{ $period->ends_at }}</div></td>
                            @foreach (\App\Livewire\Tenant\Timetable\Manage::DAYS as $day)
                                @php $entry = $timetable->entries->first(fn($e) => $e->day_of_week === $day && $e->timetable_period_id === $period->id); @endphp
                                <td class="px-3 py-2 align-top">
                                    @if ($editingDay === $day && $editingPeriodId === $period->id)
                                        <div class="space-y-1 w-40">
                                            <select wire:model="cellSubjectId" class="w-full rounded border-slate-300 text-xs"><option value="">Subject&hellip;</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select>
                                            <select wire:model="cellTeacherId" class="w-full rounded border-slate-300 text-xs"><option value="">Teacher&hellip;</option>@foreach ($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>@endforeach</select>
                                            @error('cellTeacherId') <div class="text-red-600 text-xs">{{ $message }}</div> @enderror
                                            <div class="flex gap-1"><button wire:click="saveCell" class="flex-1 rounded bg-teal-700 text-white text-xs py-1">Save</button><button wire:click="closeCell" class="flex-1 rounded border border-slate-300 text-xs py-1">Cancel</button></div>
                                        </div>
                                    @else
                                        <button wire:click="openCell('{{ $day }}', {{ $period->id }})" class="w-full text-left hover:bg-slate-50 rounded p-1 -m-1">
                                            @if ($entry)<div class="text-xs font-medium text-slate-800">{{ $entry->subject->name }}</div><div class="text-xs text-slate-500">{{ $entry->teacher->user->name }}</div>@else<span class="text-xs text-slate-300">+ Add</span>@endif
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
