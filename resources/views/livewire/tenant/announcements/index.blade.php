<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-slate-800">Announcements</h1>
        @can('create', \App\Domain\Tenant\Models\Announcement::class)
            <a href="{{ route('tenant.announcements.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">+ New Announcement</a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="space-y-3">
        @forelse ($announcements as $announcement)
            <div wire:key="announcement-{{ $announcement->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm {{ $announcement->is_read_count > 0 ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="font-semibold text-slate-800">{{ $announcement->title }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $announcement->published_at->format('d M Y, H:i') }} &middot; {{ str($announcement->audience_type)->headline() }}</p>
                    </div>
                    @if ($announcement->is_read_count === 0)
                        <button wire:click="markRead({{ $announcement->id }})" class="text-xs text-teal-700 hover:underline">Mark as read</button>
                    @endif
                </div>
                <p class="text-sm text-slate-600 mt-3">{{ $announcement->body }}</p>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">No announcements yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
