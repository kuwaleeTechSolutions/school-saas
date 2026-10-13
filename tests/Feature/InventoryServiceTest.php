<?php

namespace Tests\Feature;

use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\InventoryItem;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Services\InventoryService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * Section 46 — the stock ledger is append-only; current stock is always
 * the SUM of history, never a separately maintained counter. Verifies
 * receipts/issues/the insufficient-stock guard all compute correctly.
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected InventoryItem $item;
    protected User $actor;

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

        $this->item = InventoryItem::create(['school_id' => $this->school->id, 'name' => 'Whiteboard Markers', 'category' => 'consumable', 'unit' => 'box', 'reorder_level' => 5]);
        $this->actor = User::factory()->forSchool($this->school)->create();
    }

    /** @test */
    public function receiving_stock_increases_current_stock(): void
    {
        $service = app(InventoryService::class);
        $service->receive($this->item, 50, 'Initial stock', $this->actor);

        $this->assertEquals(50, $this->item->currentStock());
    }

    /** @test */
    public function issuing_stock_decreases_current_stock(): void
    {
        $service = app(InventoryService::class);
        $service->receive($this->item, 50, 'Initial stock', $this->actor);
        $service->issue($this->item, 20, 'Issued to Grade 1', $this->actor);

        $this->assertEquals(30, $this->item->currentStock());
    }

    /** @test */
    public function issuing_more_than_available_is_rejected(): void
    {
        $service = app(InventoryService::class);
        $service->receive($this->item, 10, 'Initial stock', $this->actor);

        $this->expectException(RuntimeException::class);
        $service->issue($this->item, 20, 'Over-issue attempt', $this->actor);
    }

    /** @test */
    public function current_stock_is_always_the_sum_of_the_ledger_never_a_drifting_counter(): void
    {
        $service = app(InventoryService::class);
        $service->receive($this->item, 100, null, $this->actor);
        $service->issue($this->item, 30, null, $this->actor);
        $service->receive($this->item, 10, null, $this->actor);
        $service->issue($this->item, 5, null, $this->actor);

        // 100 - 30 + 10 - 5 = 75, computed fresh from the ledger every time.
        $this->assertEquals(75, $this->item->currentStock());
    }

    /** @test */
    public function below_reorder_level_is_correctly_flagged(): void
    {
        $service = app(InventoryService::class);
        $service->receive($this->item, 3, null, $this->actor); // reorder_level is 5

        $this->assertTrue($this->item->isBelowReorderLevel());
    }
}
