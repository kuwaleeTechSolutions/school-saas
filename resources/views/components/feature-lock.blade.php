<x-layouts.app>
    <div class="max-w-lg mx-auto text-center py-16">
        <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">&#128274;</div>
        <h1 class="text-lg font-semibold text-slate-800">Feature unavailable</h1>
        <p class="mt-2 text-sm text-slate-500">This feature is not included in your current plan.</p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('tenant.billing.plans') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">View Plans</a>
            @can('school.billing.upgrade')
                <a href="{{ route('tenant.billing.upgrade') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Upgrade Plan</a>
            @else
                <span class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-500">Contact School Administrator</span>
            @endcan
        </div>
    </div>
</x-layouts.app>
