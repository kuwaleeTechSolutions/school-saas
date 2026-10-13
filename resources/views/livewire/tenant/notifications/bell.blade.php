<div class="relative" x-data @click.outside="$wire.open = false">
    <button wire:click="toggle" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100">
        <span>&#128276;</span>
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-medium text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg z-50">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-700">Notifications</span>
                <a href="{{ route('tenant.notifications.index') }}" class="text-xs text-teal-700 hover:underline">View all</a>
            </div>
            <div class="max-h-80 overflow-y-auto">
                @forelse ($recent as $notification)
                    <div wire:key="bell-notif-{{ $notification->id }}" class="px-4 py-3 border-b border-slate-50 last:border-0 {{ $notification->read_at ? 'opacity-60' : '' }}">
                        <div class="text-sm font-medium text-slate-800">{{ $notification->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $notification->created_at->diffForHumans() }}</div>
                        @unless ($notification->read_at)
                            <button wire:click="markRead({{ $notification->id }})" class="text-xs text-teal-700 hover:underline mt-1">Mark read</button>
                        @endunless
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-slate-400">No notifications yet.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
