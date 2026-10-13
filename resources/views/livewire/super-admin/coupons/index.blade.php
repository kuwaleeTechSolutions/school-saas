<div>
    <div class="flex items-center justify-between mb-6"><h1 class="text-lg font-semibold text-slate-800">Coupons</h1>
        @can('create', \App\Domain\Platform\Models\Coupon::class)<a href="{{ route('super-admin.coupons.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">+ New Coupon</a>@endcan</div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Code</th><th class="px-4 py-3 text-left font-medium text-slate-600">Discount</th><th class="px-4 py-3 text-left font-medium text-slate-600">Validity</th><th class="px-4 py-3 text-left font-medium text-slate-600">Redemptions</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($coupons as $coupon)
            <tr wire:key="coupon-{{ $coupon->id }}"><td class="px-4 py-3 font-mono font-medium text-slate-800">{{ $coupon->code }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $coupon->discount_type === 'percentage' ? $coupon->value.'%' : number_format($coupon->value, 2) }}</td>
                <td class="px-4 py-3 text-slate-600">@if ($coupon->valid_from || $coupon->valid_until){{ $coupon->valid_from?->format('d M Y') ?? 'Any' }} &ndash; {{ $coupon->valid_until?->format('d M Y') ?? 'No expiry' }}@else Always valid @endif</td>
                <td class="px-4 py-3 text-slate-600">{{ $coupon->redemptions_count }}{{ $coupon->max_redemptions ? ' / '.$coupon->max_redemptions : '' }}</td>
                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="px-4 py-3 text-right">@can('update', $coupon)@if ($coupon->is_active)<button wire:click="deactivate({{ $coupon->id }})" wire:confirm="Deactivate this coupon?" class="text-red-600 hover:underline">Deactivate</button>@endif @endcan</td></tr>
        @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No coupons yet.</td></tr>
        @endforelse
        </tbody></table></div>
    <div class="mt-4">{{ $coupons->links() }}</div>
</div>
