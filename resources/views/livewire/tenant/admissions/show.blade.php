<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">{{ $application->applicantFullName() }}</h1><p class="text-sm text-slate-500">Applying for {{ $application->applying_for_class }} &middot; {{ $application->academicSession->name }}</p></div>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-slate-100 text-slate-700">{{ str($application->stage)->headline() }}</span>
    </div>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @error('transition') <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs uppercase text-slate-500">Guardian</div><div class="mt-1 text-slate-800">{{ $application->guardian_name }}</div><div class="text-sm text-slate-500">{{ $application->guardian_phone }}</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs uppercase text-slate-500">Application Fee</div><div class="mt-1 text-slate-800">{{ number_format($application->application_fee_amount, 2) }} &middot; {{ $application->application_fee_paid ? 'Paid' : 'Unpaid' }}</div></div>
        @if ($application->student)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 col-span-2"><div class="text-xs uppercase text-emerald-600">Admitted Student Record</div><div class="mt-1 text-emerald-800">{{ $application->student->fullName() }} &middot; Admission #{{ $application->student->admission_number }}</div><a href="{{ route('tenant.students.show', $application->student) }}" class="text-xs text-emerald-700 hover:underline">View student profile &rarr;</a></div>
        @endif
    </div>
    @can('transition', $application)
        @if (! empty($availableNextStages))
            <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-3">Move to next stage</h2>
                @if (in_array('rejected', $availableNextStages))
                    <div class="mb-3"><label class="block text-sm font-medium text-slate-700">Rejection reason (required only if rejecting)</label><input wire:model="rejectionReason" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('rejectionReason') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
                @endif
                <div class="mb-3"><label class="block text-sm font-medium text-slate-700">Notes (optional)</label><input wire:model="transitionNotes" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($availableNextStages as $stage)
                        <button wire:click="transitionTo('{{ $stage }}')" wire:confirm="Move this application to '{{ str($stage)->headline() }}'?" @class(['rounded-lg px-4 py-2 text-sm', 'bg-teal-700 text-white' => ! in_array($stage, ['rejected', 'cancelled']), 'border border-red-300 text-red-700' => in_array($stage, ['rejected', 'cancelled'])])>{{ str($stage)->headline() }}</button>
                    @endforeach
                </div>
            </div>
        @endif
    @endcan
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Stage History</h2>
        @forelse ($application->stageHistories as $history)
            <div class="flex items-start gap-3 py-2 border-b border-slate-100 last:border-0 text-sm">
                <div class="text-slate-400 w-32 shrink-0">{{ $history->created_at->format('d M, H:i') }}</div>
                <div><span class="text-slate-600">{{ $history->from_stage ? str($history->from_stage)->headline() : 'Created' }} &rarr; <strong>{{ str($history->to_stage)->headline() }}</strong></span>@if ($history->notes)<div class="text-xs text-slate-500 mt-0.5">{{ $history->notes }}</div>@endif</div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No stage changes yet.</p>
        @endforelse
    </div>
</div>
