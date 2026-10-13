<?php

namespace App\Domain\Shared\Notification\Contracts;

use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\NotificationDelivery;

/**
 * Section 41 — "Support multiple channels: in-app, email, SMS, WhatsApp."
 * Every channel implementation attempts delivery and returns the resulting
 * NotificationDelivery row — it never throws for a routine failure (e.g. no
 * phone number on file); it records 'failed' with a reason instead, so one
 * channel's failure never blocks the others from being attempted.
 */
interface NotificationChannelContract
{
    public function channelName(): string;

    public function send(Notification $notification): NotificationDelivery;
}
