<?php

namespace App\Livewire\Tenant\Notifications;

use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Services\NotificationService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public function markRead(int $notificationId, NotificationService $notificationService): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($notificationId);
        $this->authorize('update', $notification);
        $notificationService->markRead($notification);
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.tenant.notifications.index', ['notifications' => $notifications]);
    }
}
