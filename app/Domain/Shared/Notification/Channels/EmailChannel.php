<?php

namespace App\Domain\Shared\Notification\Channels;

use App\Domain\Shared\Notification\Contracts\NotificationChannelContract;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\NotificationDelivery;
use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * A genuinely functional channel — uses Laravel's own Mail facade (SMTP
 * config from .env), no external gateway/API key required. Unlike SMS/
 * WhatsApp, email requires no Stage 8 integration work to be real.
 */
class EmailChannel implements NotificationChannelContract
{
    public function channelName(): string
    {
        return 'email';
    }

    public function send(Notification $notification): NotificationDelivery
    {
        $delivery = NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => 'email',
            'status' => 'queued',
        ]);

        $recipient = User::withoutGlobalScopes()->find($notification->user_id);

        if (! $recipient || ! $recipient->email) {
            $delivery->update(['status' => 'failed', 'failure_reason' => 'Recipient has no email address on file.']);
            return $delivery;
        }

        try {
            Mail::to($recipient->email)->queue(new NotificationMail($notification));
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $delivery->update(['status' => 'failed', 'failure_reason' => substr($e->getMessage(), 0, 500)]);
        }

        return $delivery;
    }
}
