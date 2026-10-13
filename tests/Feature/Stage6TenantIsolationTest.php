<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Announcement;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section I.4 / O.2 — the mandatory tenant isolation test applied to
 * Stage 6's new modules (Announcements, Notifications), per Section M.2's
 * per-module requirement.
 */
class Stage6TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected Announcement $announcementB;
    protected Notification $notificationB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = $this->buildSchool('stage6-school-a');
        $this->schoolB = $this->buildSchool('stage6-school-b');

        app(TenantContext::class)->set($this->schoolB);
        $userB = User::factory()->forSchool($this->schoolB)->create();

        $this->announcementB = Announcement::create([
            'school_id' => $this->schoolB->id, 'title' => 'School B Notice', 'body' => 'Body',
            'audience_type' => 'school_wide', 'published_by' => $userB->id, 'published_at' => now(),
        ]);

        $this->notificationB = Notification::create([
            'school_id' => $this->schoolB->id, 'user_id' => $userB->id,
            'type' => 'test.event', 'title' => 'Title', 'body' => 'Body',
        ]);
    }

    protected function buildSchool(string $slug): School
    {
        $school = School::factory()->create(['slug' => $slug]);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $school->subscriptions()->create([
            'ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly',
            'status' => 'active', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true,
        ]);

        return $school;
    }

    /** @test */
    public function announcements_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(Announcement::find($this->announcementB->id));
        $this->assertNotContains($this->announcementB->id, Announcement::pluck('id')->all());
    }

    /** @test */
    public function notifications_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(Notification::find($this->notificationB->id));
        $this->assertNotContains($this->notificationB->id, Notification::pluck('id')->all());
    }
}
