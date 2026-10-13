<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Visitor Management</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\VisitorLog::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Check In Visitor</h2>
            <form wire:submit="checkIn" class="grid grid-cols-2 gap-3">
                <input wire:model="visitorName" type="text" placeholder="Visitor name *" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="visitorPhone" type="text" placeholder="Phone" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="purpose" type="text" placeholder="Purpose of visit *" class="rounded-lg border-slate-300 text-sm">
                <input wire:model="whomToMeet" type="text" placeholder="Whom to meet" class="rounded-lg border-slate-300 text-sm">
                <button type="submit" class="col-span-2 rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Check In &amp; Generate Gate Pass</button>
            </form>
            @error('visitorName') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
            @error('purpose') <p class="text-red-600 text-sm mt-2">{{ $message }}</p> @enderror
        </div>
    @endcan
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Currently On Premises</h2>
        <div class="overflow-hidden rounded-lg border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-medium text-slate-600">Gate Pass</th><th class="px-3 py-2 text-left font-medium text-slate-600">Visitor</th><th class="px-3 py-2 text-left font-medium text-slate-600">Purpose</th><th class="px-3 py-2 text-left font-medium text-slate-600">Checked In</th><th class="px-3 py-2 text-right font-medium text-slate-600">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($currentlyIn as $log)
                        <tr wire:key="visitor-{{ $log->id }}"><td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $log->gate_pass_number }}</td><td class="px-3 py-2 text-slate-700">{{ $log->visitor_name }}</td><td class="px-3 py-2 text-slate-600">{{ $log->purpose }}</td><td class="px-3 py-2 text-slate-500">{{ $log->checked_in_at->format('H:i') }}</td><td class="px-3 py-2 text-right"><button wire:click="checkOut({{ $log->id }})" class="text-teal-700 hover:underline">Check Out</button></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">No visitors currently on premises.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
