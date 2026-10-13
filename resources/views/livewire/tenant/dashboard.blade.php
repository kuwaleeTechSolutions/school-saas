<div>
    <h1 class="text-lg font-semibold text-slate-800">Welcome, {{ auth()->user()->name }}</h1>
    <p class="text-sm text-slate-500 mb-6">{{ $school->name }}</p>
    @if ($subscription && $subscription->status === 'trial' && $subscription->trial_ends_at)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Your trial ends in {{ now()->diffInDays($subscription->trial_ends_at, false) }} days.</div>
    @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Current Plan</div><div class="mt-1 text-xl font-semibold text-slate-800">{{ $subscription?->plan?->name ?? '—' }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Subscription Status</div><div class="mt-1 text-xl font-semibold text-slate-800">{{ $subscription ? str($subscription->status)->headline() : '—' }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs uppercase tracking-wide text-slate-500">Modules Enabled</div><div class="mt-1 text-xl font-semibold text-slate-800">{{ $enabledModules->count() }}</div></div>
    </div>
</div>
