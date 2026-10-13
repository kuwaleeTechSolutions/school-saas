<div class="max-w-4xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-1">Analytics</h1>
    <p class="text-xs text-slate-400 mb-6">Figures refresh every 10 minutes.</p>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-slate-700 mb-3">Attendance, last 14 days (daily mode)</h2>
            @forelse ($attendance as $d)<div class="flex items-center gap-2 text-xs mb-1"><span class="w-20 text-slate-500">{{ \Illuminate\Support\Carbon::parse($d['date'])->format('d M') }}</span><div class="flex-1 h-3 rounded bg-slate-100"><div class="h-3 rounded bg-teal-600" style="width: {{ $d['percent'] ?? 0 }}%"></div></div><span class="w-12 text-right text-slate-600">{{ $d['percent'] !== null ? $d['percent'].'%' : '—' }}</span></div>@empty<p class="text-sm text-slate-500">No attendance recorded in this period.</p>@endforelse</div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-slate-700 mb-3">Fees collected by month</h2>
            @php $max = max(1, ...array_values($fees ?: [1])); @endphp
            @forelse ($fees as $month => $amount)<div class="flex items-center gap-2 text-xs mb-1"><span class="w-16 text-slate-500">{{ $month }}</span><div class="flex-1 h-3 rounded bg-slate-100"><div class="h-3 rounded bg-emerald-600" style="width: {{ round($amount / $max * 100) }}%"></div></div><span class="w-24 text-right text-slate-600">{{ number_format($amount, 2) }}</span></div>@empty<p class="text-sm text-slate-500">No payments in this period.</p>@endforelse</div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-slate-700 mb-3">Students per class (current session)</h2>
            @forelse ($distribution as $class => $n)<div class="flex justify-between text-sm py-1 border-b border-slate-100 last:border-0"><span class="text-slate-700">{{ $class }}</span><span class="text-slate-600">{{ $n }}</span></div>@empty<p class="text-sm text-slate-500">No active enrollments.</p>@endforelse</div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="text-sm font-semibold text-slate-700 mb-3">Admissions by stage</h2>
            @forelse ($funnel as $stage => $n)<div class="flex justify-between text-sm py-1 border-b border-slate-100 last:border-0"><span class="text-slate-700">{{ str($stage)->headline() }}</span><span class="text-slate-600">{{ $n }}</span></div>@empty<p class="text-sm text-slate-500">No applications yet.</p>@endforelse</div>
    </div>
</div>
