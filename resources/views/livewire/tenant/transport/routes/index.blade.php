<div>
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Transport Routes</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\TransportRoute::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Add Route</h2>
            <form wire:submit="addRoute" class="grid grid-cols-3 gap-3">
                <input wire:model="name" type="text" placeholder="Route name" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="vehicleNumber" type="text" placeholder="Vehicle number" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="driverName" type="text" placeholder="Driver name" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="driverPhone" type="text" placeholder="Driver phone" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="monthlyFee" type="number" step="0.01" placeholder="Monthly fee" class="rounded-lg border-slate-300 text-sm">
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Add Route</button>
            </form>
            @error('name') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
        </div>
    @endcan
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Route</th><th class="px-4 py-3 text-left font-medium text-slate-600">Vehicle</th><th class="px-4 py-3 text-left font-medium text-slate-600">Driver</th><th class="px-4 py-3 text-left font-medium text-slate-600">Monthly Fee</th><th class="px-4 py-3 text-left font-medium text-slate-600">Students</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($routes as $route)
                    <tr wire:key="route-{{ $route->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $route->name }}</td><td class="px-4 py-3 text-slate-600">{{ $route->vehicle_number ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ $route->driver_name ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ number_format($route->monthly_fee, 2) }}</td><td class="px-4 py-3 text-slate-600">{{ $route->assignments_count }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No routes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
