<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Shared\Notification\Channels\EmailChannel;
use App\Domain\Shared\Notification\Channels\InAppChannel;
use App\Domain\Shared\Notification\Channels\LoggedStubChannel;
use App\Domain\Shared\Notification\Contracts\NotificationChannelContract;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Section 41 — the single entry point for "trigger configurable
 * notification," used by Stage 3/4's absence/results-published hooks and
 * by AnnouncementService. Deliberately NOT gated by
 * EntitlementServiceContract — an in-app notification (and its audit-log-
 * like delivery record) is core data-safety/communication plumbing, the
 * same way school_audit_logs is never withheld by package — only the
 * ADMIN-FACING authoring screens (Announcements\Create) are
 * package-gated via `communication.create`.
 */
class NotificationService
{
    /** @return NotificationChannelContract[] */
    protected function allChannels(): array
    {
        return [
            'in_app' => new InAppChannel(),
            'email' => new EmailChannel(),
            'sms' => new LoggedStubChannel('sms'),
            'whatsapp' => new LoggedStubChannel('whatsapp'),
        ];
    }

    /**
     * @param  string[]  $channels  Which channels to attempt; 'in_app' is
     *                              always attempted regardless of this list,
     *                              since the Notification row itself is the
     *                              in-app record.
     */
    public function dispatch(User $recipient, string $type, string $title, string $body, array $data = [], array $channels = []): Notification
    {
        return DB::transaction(function () use ($recipient, $type, $title, $body, $data, $channels) {
            $notification = Notification::create([
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            $channelsToAttempt = array_unique(array_merge(['in_app'], $channels));
            $available = $this->allChannels();

            foreach ($channelsToAttempt as $channelName) {
                if (! isset($available[$channelName])) {
                    continue;
                }

                if ($channelName !== 'in_app' && $this->isOptedOut($recipient->id, $type, $channelName)) {
                    continue;
                }

                $available[$channelName]->send($notification);
            }

            return $notification->fresh('deliveries');
        });
    }

    protected function isOptedOut(int $userId, string $type, string $channel): bool
    {
        return NotificationPreference::where('user_id', $userId)
            ->where('notification_type', $type)
            ->where('channel', $channel)
            ->where('enabled', false)
            ->exists();
    }

    public function markRead(Notification $notification): Notification
    {
        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->fresh();
    }

    public function unreadCountFor(int $userId): int
    {
        return Notification::where('user_id', $userId)->whereNull('read_at')->count();
    }
}
