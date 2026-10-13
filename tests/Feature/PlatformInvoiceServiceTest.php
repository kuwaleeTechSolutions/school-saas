<?php
namespace Tests\Feature;
use App\Domain\Platform\Models\Coupon;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Services\PlatformInvoiceService;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** Section 16 — one invoice per period, idempotent, correct period end, coupon as a discount line item. */
class PlatformInvoiceServiceTest extends TestCase
{
    use RefreshDatabase;
    protected School $school; protected $subscription;
    protected function setUp(): void { parent::setUp(); $this->seed(); [$this->school, $this->subscription] = $this->makeSchool(); }
    protected function makeSchool(?string $slug = null): array
    {
        $school = School::factory()->create($slug ? ['slug' => $slug] : []);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $sub = $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return [$school, $sub];
    }

    /** @test */ public function the_invoice_uses_the_plans_configured_price(): void
    {
        $inv = app(PlatformInvoiceService::class)->generateForPeriod($this->subscription, Carbon::parse('2026-06-01'));
        $this->assertEquals((float) $this->subscription->plan->prices()->where('billing_cycle','monthly')->first()->price, $inv->total_amount);
        $this->assertSame('unpaid', $inv->status);
    }
    /** @test */ public function generation_is_idempotent_per_period(): void
    {
        $s = app(PlatformInvoiceService::class);
        $a = $s->generateForPeriod($this->subscription, Carbon::parse('2026-06-01')); $b = $s->generateForPeriod($this->subscription, Carbon::parse('2026-06-01'));
        $this->assertSame($a->id, $b->id); $this->assertSame(1, PlatformInvoice::count());
    }
    /** @test */ public function a_monthly_period_ends_the_day_before_the_next_starts(): void { $this->assertSame('2026-06-30', app(PlatformInvoiceService::class)->generateForPeriod($this->subscription, Carbon::parse('2026-06-01'))->period_ends_on->toDateString()); }
    /** @test */ public function a_coupon_becomes_a_discount_line_item(): void
    {
        Coupon::factory()->create(['code'=>'SAVE15','value'=>15]);
        $inv = app(PlatformInvoiceService::class)->generateForPeriod($this->subscription, Carbon::parse('2026-06-01'), 'SAVE15');
        $this->assertGreaterThan(0, $inv->discount_amount);
        $this->assertTrue($inv->lineItems->contains(fn ($i) => $i->line_type === 'discount'));
        $this->assertEquals($inv->subtotal_amount - $inv->discount_amount, $inv->total_amount);
        $this->assertEquals($inv->total_amount, $inv->balance_amount);
    }
    /** @test */ public function invoice_numbers_are_unique(): void
    {
        $s = app(PlatformInvoiceService::class); [, $sub2] = $this->makeSchool();
        $this->assertNotEquals($s->generateForPeriod($this->subscription, Carbon::parse('2026-06-01'))->invoice_number, $s->generateForPeriod($sub2, Carbon::parse('2026-06-01'))->invoice_number);
    }
}
