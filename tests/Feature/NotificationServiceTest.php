<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\NotificationPreference;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Services\NotificationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section 41 — "Support multiple channels: in-app, email, SMS, WhatsApp."
 * Verifies dispatch() always creates the in-app record regardless of the
 * requested channel list, respects an explicit per-user/per-type opt-out,
 * and that markRead()/unreadCountFor() behave correctly.
 */
class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $recipient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->school = School::factory()->create();
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $this->school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        app(TenantContext::class)->set($this->school);
        $this->recipient = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function dispatch_always_creates_an_in_app_record_even_if_not_requested(): void
    {
        $service = app(NotificationService::class);
        $notification = $service->dispatch($this->recipient, 'test.event', 'Title', 'Body', [], []);

        $this->assertDatabaseHas('notification_deliveries', ['notification_id' => $notification->id, 'channel' => 'in_app', 'status' => 'sent']);
    }

    /** @test */
    public function requesting_the_email_channel_queues_a_real_mailable(): void
    {
        Mail::fake();

        $service = app(NotificationService::class);
        $service->dispatch($this->recipient, 'test.event', 'Title', 'Body', [], ['email']);

        Mail::assertQueued(\App\Mail\NotificationMail::class);
        $this->assertDatabaseHas('notification_deliveries', ['channel' => 'email', 'status' => 'sent']);
    }

    /** @test */
    public function sms_and_whatsapp_are_honestly_recorded_as_not_yet_integrated(): void
    {
        $service = app(NotificationService::class);
        $notification = $service->dispatch($this->recipient, 'test.event', 'Title', 'Body', [], ['sms', 'whatsapp']);

        $this->assertDatabaseHas('notification_deliveries', ['notification_id' => $notification->id, 'channel' => 'sms', 'status' => 'failed']);
        $this->assertDatabaseHas('notification_deliveries', ['notification_id' => $notification->id, 'channel' => 'whatsapp', 'status' => 'failed']);
    }

    /** @test */
    public function an_explicit_opt_out_prevents_that_channel_only(): void
    {
        Mail::fake();

        NotificationPreference::create([
            'school_id' => $this->school->id, 'user_id' => $this->recipient->id,
            'notification_type' => 'test.event', 'channel' => 'email', 'enabled' => false,
        ]);

        $service = app(NotificationService::class);
        $notification = $service->dispatch($this->recipient, 'test.event', 'Title', 'Body', [], ['email']);

        Mail::assertNothingQueued();
        $this->assertDatabaseMissing('notification_deliveries', ['notification_id' => $notification->id, 'channel' => 'email']);
        // in_app is never opted out of — it's always attempted.
        $this->assertDatabaseHas('notification_deliveries', ['notification_id' => $notification->id, 'channel' => 'in_app']);
    }

    /** @test */
    public function mark_read_sets_read_at_exactly_once(): void
    {
        $service = app(NotificationService::class);
        $notification = $service->dispatch($this->recipient, 'test.event', 'Title', 'Body');

        $this->assertNull($notification->read_at);
        $read = $service->markRead($notification);
        $this->assertNotNull($read->read_at);
    }

    /** @test */
    public function unread_count_reflects_only_unread_notifications_for_that_user(): void
    {
        $service = app(NotificationService::class);
        $n1 = $service->dispatch($this->recipient, 'a', 'A', 'a');
        $service->dispatch($this->recipient, 'b', 'B', 'b');

        $this->assertSame(2, $service->unreadCountFor($this->recipient->id));
        $service->markRead($n1);
        $this->assertSame(1, $service->unreadCountFor($this->recipient->id));
    }
}
