<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\InventoryItem;
use App\Domain\Tenant\Models\LibraryBook;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\StaffProfile;
use App\Domain\Tenant\Models\TransportRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Section I.4 / O.2 — the mandatory tenant isolation test applied to
 * Stage 7's new modules (Library, Transport, Inventory, HR), per Section
 * M.2's per-module requirement.
 */
class Stage7TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;
    protected School $schoolB;
    protected LibraryBook $bookB;
    protected TransportRoute $routeB;
    protected InventoryItem $itemB;
    protected StaffProfile $staffB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->schoolA = $this->buildSchool('stage7-school-a');
        $this->schoolB = $this->buildSchool('stage7-school-b');

        app(TenantContext::class)->set($this->schoolB);

        $this->bookB = LibraryBook::create(['school_id' => $this->schoolB->id, 'title' => 'School B Book']);
        $this->routeB = TransportRoute::create(['school_id' => $this->schoolB->id, 'name' => 'School B Route', 'is_active' => true]);
        $this->itemB = InventoryItem::create(['school_id' => $this->schoolB->id, 'name' => 'School B Item']);

        $userB = User::factory()->forSchool($this->schoolB)->create();
        $this->staffB = StaffProfile::factory()->create(['school_id' => $this->schoolB->id, 'user_id' => $userB->id]);
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
    public function library_books_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(LibraryBook::find($this->bookB->id));
    }

    /** @test */
    public function transport_routes_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(TransportRoute::find($this->routeB->id));
    }

    /** @test */
    public function inventory_items_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(InventoryItem::find($this->itemB->id));
    }

    /** @test */
    public function staff_profiles_are_never_visible_across_schools(): void
    {
        app(TenantContext::class)->set($this->schoolA);
        $this->assertNull(StaffProfile::find($this->staffB->id));
    }
}
