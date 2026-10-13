<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Leave Requests</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\LeaveRequest::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Request Leave</h2>
            <form wire:submit="requestLeave" class="space-y-3">
                <select wire:model="staffProfileId" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select staff member&hellip;</option>@foreach ($staffList as $staff)<option value="{{ $staff->id }}">{{ $staff->user->name }}</option>@endforeach</select>
                @error('staffProfileId') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <div class="grid grid-cols-2 gap-3"><input wire:model="startsOn" type="date" class="rounded-lg border-slate-300 text-sm"><input wire:model="endsOn" type="date" class="rounded-lg border-slate-300 text-sm"></div>
                @error('endsOn') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <textarea wire:model="reason" rows="2" placeholder="Reason" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                @error('reason') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Request Leave</button>
            </form>
        </div>
    @endcan
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Staff</th><th class="px-4 py-3 text-left font-medium text-slate-600">Dates</th><th class="px-4 py-3 text-left font-medium text-slate-600">Status</th><th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($leaveRequests as $leave)
                    <tr wire:key="leave-{{ $leave->id }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $leave->staffProfile->user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $leave->starts_on->format('d M') }} - {{ $leave->ends_on->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ str($leave->status)->headline() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if ($leave->status === 'requested')
                                @can('approve', $leave)<button wire:click="approve({{ $leave->id }})" class="text-emerald-700 hover:underline">Approve</button><button wire:click="startReject({{ $leave->id }})" class="text-red-600 hover:underline">Reject</button>@endcan
                            @endif
                        </td>
                    </tr>
                    @if ($rejectingLeaveId === $leave->id)
                        <tr><td colspan="4" class="px-4 py-3 bg-slate-50"><div class="flex gap-3"><input wire:model="rejectionReason" type="text" placeholder="Rejection reason" class="flex-1 rounded-lg border-slate-300 text-sm"><button wire:click="confirmReject" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs text-white">Confirm Reject</button></div></td></tr>
                    @endif
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No leave requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
