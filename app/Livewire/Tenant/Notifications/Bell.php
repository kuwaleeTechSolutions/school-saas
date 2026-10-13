<?php

namespace App\Livewire\Tenant\Notifications;

use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Services\NotificationService;
use Livewire\Component;

/**
 * Mounted directly in the app layout header (see Stage 6 README for the
 * one-line snippet to add to components/layouts/app.blade.php) — shows the
 * live unread count and the 5 most recent notifications in a dropdown.
 */
class Bell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markRead(int $notificationId, NotificationService $notificationService): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($notificationId);
        $notificationService->markRead($notification);
    }

    public function render(NotificationService $notificationService)
    {
        return view('livewire.tenant.notifications.bell', [
            'unreadCount' => $notificationService->unreadCountFor(auth()->id()),
            'recent' => Notification::where('user_id', auth()->id())->orderByDesc('created_at')->limit(5)->get(),
        ]);
    }
}
