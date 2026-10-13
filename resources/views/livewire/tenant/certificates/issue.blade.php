<div class="max-w-2xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Issue Certificate</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
        <form wire:submit="issue" class="grid grid-cols-2 gap-3 items-end">
            <div><label class="block text-xs font-medium text-slate-700">Template</label><select wire:model="templateId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select>@error('templateId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div><label class="block text-xs font-medium text-slate-700">Student</label><select wire:model="studentId" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Select&hellip;</option>@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->fullName() }}</option>@endforeach</select>@error('studentId') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror</div>
            <div class="col-span-2"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Issue Certificate</button></div>
        </form>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">Recently Issued</h2>
        @forelse ($recentlyIssued as $cert)
            <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                <span>{{ $cert->student->fullName() }} &middot; {{ $cert->template->name }} &middot; {{ $cert->certificate_number }}</span>
                @if ($cert->pdf_status === 'generated')<a href="{{ route('tenant.certificates.download', $cert) }}" class="text-teal-700 hover:underline">Download</a>@else<span class="text-slate-400 text-xs">{{ str($cert->pdf_status)->headline() }}</span>@endif
            </div>
        @empty
            <p class="text-sm text-slate-500">No certificates issued yet.</p>
        @endforelse
    </div>
</div>
