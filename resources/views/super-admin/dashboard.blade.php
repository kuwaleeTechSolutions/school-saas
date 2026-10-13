<x-layouts.super-admin>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Platform Overview</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Total Schools</div><div class="mt-1 text-2xl font-semibold text-slate-800">{{ $totalSchools }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Active</div><div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $activeSchools }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Trial</div><div class="mt-1 text-2xl font-semibold text-blue-700">{{ $trialSchools }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Suspended</div><div class="mt-1 text-2xl font-semibold text-red-700">{{ $suspendedSchools }}</div></div>
    </div>
</x-layouts.super-admin>
