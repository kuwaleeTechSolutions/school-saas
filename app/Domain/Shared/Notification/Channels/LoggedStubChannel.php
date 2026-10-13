<?php

namespace App\Domain\Shared\Notification\Channels;

use App\Domain\Shared\Notification\Contracts\NotificationChannelContract;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\NotificationDelivery;
use Illuminate\Support\Facades\Log;

/**
 * Section 41 lists SMS and WhatsApp as required channels, but actually
 * sending either requires a paid gateway account and API credentials —
 * genuine third-party integration work that belongs in Stage 8 alongside
 * payment gateways, not fabricated here as fake-but-silent success.
 *
 * This stub is deliberately HONEST about that: it writes a real
 * `notification_deliveries` row with status 'failed' and a clear reason,
 * and logs the message content so you can verify the trigger/content logic
 * end-to-end right now — but it never claims a message was actually sent
 * over SMS/WhatsApp when no such integration exists (Section 144 — no fake
 * features). Swap this class's `send()` body for a real gateway SDK call in
 * Stage 8 and nothing else in NotificationService needs to change.
 */
class LoggedStubChannel implements NotificationChannelContract
{
    public function __construct(protected string $channel)
    {
    }

    public function channelName(): string
    {
        return $this->channel;
    }

    public function send(Notification $notification): NotificationDelivery
    {
        Log::info("[{$this->channel} stub] Would send to user #{$notification->user_id}: {$notification->title} — {$notification->body}");

        return NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => $this->channel,
            'status' => 'failed',
            'failure_reason' => ucfirst($this->channel) . ' gateway integration is not yet configured (planned for Stage 8).',
        ]);
    }
}
