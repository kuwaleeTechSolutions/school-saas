<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Usage Metering</h1>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        @foreach ($platformTotals as $metric => $total)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">{{ str($metric)->headline() }}</div><div class="mt-1 text-2xl font-semibold text-slate-800">{{ number_format($total) }}</div><div class="text-xs text-slate-400">across all schools</div></div>
        @endforeach
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="px-5 py-3 border-b border-slate-100"><h2 class="text-sm font-semibold text-slate-700">Schools Nearing Plan Limits (&ge;90%)</h2></div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">School</th><th class="px-4 py-3 text-left font-medium text-slate-600">Plan</th><th class="px-4 py-3 text-left font-medium text-slate-600">Usage Detail</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($schoolsNearingLimits as $school)
                <tr wire:key="usage-school-{{ $school->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $school->name }}</td><td class="px-4 py-3 text-slate-600">{{ $school->activeSubscription?->plan?->name ?? '—' }}</td>
                    <td class="px-4 py-3"><div class="flex flex-wrap gap-2">@foreach ($usageService->usageFor($school) as $row)@if ($row['limit'] !== null)<span @class(['rounded-full px-2 py-0.5 text-xs','bg-red-100 text-red-700' => $row['is_near_limit'],'bg-slate-100 text-slate-600' => ! $row['is_near_limit']])>{{ str($row['metric'])->headline() }}: {{ $row['used'] }}/{{ $row['limit'] }} ({{ $row['percent'] }}%)</span>@endif @endforeach</div></td></tr>
            @empty
                <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No schools are currently near their plan limits.</td></tr>
            @endforelse
            </tbody></table>
    </div>
</div>
