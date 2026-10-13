<?php

namespace App\Domain\Shared\Notification\Channels;

use App\Domain\Shared\Notification\Contracts\NotificationChannelContract;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\NotificationDelivery;

/**
 * The in-app channel's "delivery" IS the Notification row itself — there is
 * nothing external to call, so this always succeeds instantly.
 */
class InAppChannel implements NotificationChannelContract
{
    public function channelName(): string
    {
        return 'in_app';
    }

    public function send(Notification $notification): NotificationDelivery
    {
        return NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => 'in_app',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
