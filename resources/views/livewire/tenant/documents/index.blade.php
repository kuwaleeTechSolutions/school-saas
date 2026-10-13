<div class="max-w-3xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Documents</h1>
    @if (session('success'))<div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @can('create', \App\Domain\Tenant\Models\SchoolDocument::class)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3">Upload Document</h2>
            <form wire:submit="upload" class="space-y-3">
                <input wire:model="title" type="text" placeholder="Title" class="w-full rounded-lg border-slate-300 text-sm">
                @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <input wire:model="category" type="text" placeholder="Category (e.g. Circular, Policy)" class="w-full rounded-lg border-slate-300 text-sm">
                <input wire:model="file" type="file" class="block text-sm">
                @error('file') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                <select wire:model.live="audienceType" class="w-full rounded-lg border-slate-300 text-sm"><option value="school_wide">Everyone</option><option value="role">Specific Role</option></select>
                @if ($audienceType === 'role')
                    <select wire:model="audienceRole" class="w-full rounded-lg border-slate-300 text-sm"><option value="">Select role&hellip;</option>@foreach ($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select>
                    @error('audienceRole') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                @endif
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Upload</button>
            </form>
        </div>
    @endcan
    <div class="space-y-2">
        @forelse ($documents as $document)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm flex items-center justify-between">
                <div><div class="font-medium text-slate-800">{{ $document->title }}</div><div class="text-xs text-slate-500">{{ $document->category ?? 'Uncategorized' }} &middot; {{ $document->created_at->format('d M Y') }}</div></div>
                <a href="{{ route('tenant.documents.download', $document) }}" class="text-sm text-teal-700 hover:underline">Download</a>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">No documents yet.</div>
        @endforelse
    </div>
</div>
