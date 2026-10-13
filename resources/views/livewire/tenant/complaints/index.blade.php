<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Complaints</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ session('error') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Raise a Complaint</h2>
        <form wire:submit="raise" class="space-y-3">
            <input wire:model="subject" type="text" placeholder="Subject" class="w-full rounded-lg border-slate-300 text-sm">
            @error('subject') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            <select wire:model="category" class="w-full rounded-lg border-slate-300 text-sm"><option value="academic">Academic</option><option value="facility">Facility</option><option value="behavioral">Behavioral</option><option value="transport">Transport</option><option value="fees">Fees</option><option value="other">Other</option></select>
            <textarea wire:model="description" rows="3" placeholder="Description" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
            @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Raise Complaint</button>
        </form>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Subject</th><th class="px-4 py-3 text-left font-medium text-slate-600">Category</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($complaints as $complaint)
                    <tr wire:key="complaint-{{ $complaint->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $complaint->subject }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ str($complaint->category)->headline() }}</td>
                        <td class="px-4 py-3"><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', 'bg-emerald-100 text-emerald-700' => $complaint->status === 'resolved', 'bg-slate-100 text-slate-600' => in_array($complaint->status, ['closed']), 'bg-amber-100 text-amber-700' => in_array($complaint->status, ['open', 'in_progress'])])>{{ str($complaint->status)->headline() }}</span></td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @can('update', $complaint)
                                @foreach ($complaintService->availableNextStatuses($complaint) as $next)
                                    <button wire:click="transitionComplaint({{ $complaint->id }}, '{{ $next }}')" class="text-teal-700 hover:underline">{{ str($next)->headline() }}</button>
                                @endforeach
                            @endcan
                        </td>
                    </tr>
                    @if ($resolvingComplaintId === $complaint->id)
                        <tr><td colspan="4" class="px-4 py-3 bg-slate-50">
                            <div class="flex gap-3"><input wire:model="resolutionNotes" type="text" placeholder="Resolution notes" class="flex-1 rounded-lg border-slate-300 text-sm"><button wire:click="confirmResolve" class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs text-white">Confirm Resolve</button></div>
                            @error('resolutionNotes') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </td></tr>
                    @endif
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No complaints yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $complaints->links() }}</div>
</div>
