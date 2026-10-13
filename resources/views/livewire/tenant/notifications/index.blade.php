<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-slate-800">Notifications</h1>
        <button wire:click="markAllRead" class="text-sm text-teal-700 hover:underline">Mark all as read</button>
    </div>

    <div class="space-y-2">
        @forelse ($notifications as $notification)
            <div wire:key="notif-{{ $notification->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-sm font-medium text-slate-800">{{ $notification->title }}</h2>
                        <p class="text-sm text-slate-600 mt-1">{{ $notification->body }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <button wire:click="markRead({{ $notification->id }})" class="text-xs text-teal-700 hover:underline whitespace-nowrap">Mark read</button>
                    @endunless
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">No notifications yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
