<div class="max-w-4xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Reports</h1>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <label class="block text-sm font-medium text-slate-700">Report</label>
        <select wire:model.live="key" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($available as $k => $def)<option value="{{ $k }}">{{ $def->title() }}</option>@endforeach</select>
        @error('key')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        @if (empty($available))<p class="text-sm text-slate-500 mt-3">No reports are available on your plan or role.</p>@endif
        @if ($current)
            <div class="grid grid-cols-3 gap-3 mt-4">
                @foreach ($current->filters() as $name => $f)
                    <div><label class="block text-xs font-medium text-slate-700">{{ $f['label'] }}</label>
                        @if ($f['type'] === 'select')<select wire:model="filters.{{ $name }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">All</option>@foreach ($f['options'] ?? [] as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                        @else<input wire:model="filters.{{ $name }}" type="{{ $f['type'] === 'date' ? 'date' : 'text' }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@endif
                        @error('filters.'.$name)<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror</div>
                @endforeach
            </div>
            <div class="flex gap-3 mt-4">
                <button wire:click="run" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Preview</button>
                <a href="{{ route('tenant.reports.export', ['key' => $key, 'format' => 'csv']) }}?{{ $exportQuery }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Download CSV</a>
                <a href="{{ route('tenant.reports.export', ['key' => $key, 'format' => 'pdf']) }}?{{ $exportQuery }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Download PDF</a>
            </div>
            <p class="text-xs text-slate-400 mt-2">Excel (.xlsx) export is not available yet. CSV opens in Excel.</p>
        @endif
    </div>
    @if ($preview !== null && $current)
        <div class="overflow-auto rounded-xl border border-slate-200 bg-white shadow-sm"><table class="min-w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach ($current->columns() as $h)<th class="px-3 py-2 text-left font-medium text-slate-600">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse ($preview as $row)<tr>@foreach (array_keys($current->columns()) as $c)<td class="px-3 py-2 text-slate-700">{{ $row[$c] ?? '' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($current->columns()) }}" class="px-3 py-8 text-center text-slate-500">No rows match these filters.</td></tr>@endforelse</tbody></table></div>
        <p class="text-xs text-slate-400 mt-2">Preview shows up to 100 rows{{ $truncated ? ' (more exist)' : '' }}. Downloads include up to 5,000 rows.</p>
    @endif
</div>
