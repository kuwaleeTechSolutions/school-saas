<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-lg font-semibold text-slate-800">{{ $student->fullName() }}</h1><p class="text-sm text-slate-500">Admission #{{ $student->admission_number }}</p></div>
        @can('update', $student)<a href="{{ route('tenant.students.edit', $student) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Edit</a>@endcan
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs uppercase text-slate-500">Class / Section</div><div class="mt-1 text-slate-800">@if ($student->currentEnrollment){{ $student->currentEnrollment->schoolClass->name }} - {{ $student->currentEnrollment->section->name }}@else<span class="text-slate-400">Not enrolled</span>@endif</div></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-xs uppercase text-slate-500">Status</div><div class="mt-1 text-slate-800">{{ str($student->status)->headline() }}</div></div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Guardians</h2>
        @forelse ($student->guardians as $guardian)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm"><div><span class="font-medium text-slate-800">{{ $guardian->name }}</span> <span class="text-slate-500">({{ ucfirst($guardian->pivot->relation) }})</span></div><div class="text-slate-500">{{ $guardian->phone }}</div></div>
        @empty
            <p class="text-sm text-slate-500">No guardians linked.</p>
        @endforelse
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Documents</h2>
        @forelse ($student->documents as $doc)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm"><span>{{ str($doc->type)->headline() }}</span><span class="text-slate-500">{{ $doc->original_name }}</span></div>
        @empty
            <p class="text-sm text-slate-500">No documents uploaded yet.</p>
        @endforelse
    </div>
</div>
