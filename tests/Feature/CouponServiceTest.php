<?php
namespace Tests\Feature;
use App\Domain\Platform\Models\Coupon;
use App\Domain\Platform\Services\CouponService;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** Section 16 — validity window, total/per-school caps, discount maths. */
class CouponServiceTest extends TestCase
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

    /** @test */ public function a_valid_active_coupon_passes(): void { $c = Coupon::factory()->create(['code'=>'VALID10']); $this->assertSame($c->id, app(CouponService::class)->validate('VALID10', $this->school)->id); }
    /** @test */ public function code_matching_is_case_insensitive(): void { Coupon::factory()->create(['code'=>'MIXEDCASE']); $this->assertSame('MIXEDCASE', app(CouponService::class)->validate('mixedcase', $this->school)->code); }
    /** @test */ public function an_inactive_coupon_is_rejected(): void { Coupon::factory()->create(['code'=>'OFF1','is_active'=>false]); $this->expectException(RuntimeException::class); app(CouponService::class)->validate('OFF1', $this->school); }
    /** @test */ public function an_expired_coupon_is_rejected(): void { Coupon::factory()->create(['code'=>'OLD1','valid_until'=>Carbon::yesterday()]); $this->expectException(RuntimeException::class); app(CouponService::class)->validate('OLD1', $this->school); }
    /** @test */ public function a_coupon_expiring_today_is_still_valid_until_end_of_day(): void { Coupon::factory()->create(['code'=>'TODAY1','valid_until'=>Carbon::today()]); $this->assertSame('TODAY1', app(CouponService::class)->validate('TODAY1', $this->school)->code); }
    /** @test */ public function a_future_coupon_is_rejected(): void { Coupon::factory()->create(['code'=>'SOON1','valid_from'=>Carbon::tomorrow()]); $this->expectException(RuntimeException::class); app(CouponService::class)->validate('SOON1', $this->school); }
    /** @test */ public function the_total_redemption_cap_is_enforced(): void
    {
        Coupon::factory()->create(['code'=>'CAP1','max_redemptions'=>1]);
        [$other, $otherSub] = $this->makeSchool();
        app(CouponService::class)->apply('CAP1', $other, $otherSub, 1000);
        $this->expectException(RuntimeException::class);
        app(CouponService::class)->validate('CAP1', $this->school);
    }
    /** @test */ public function the_per_school_limit_is_enforced(): void
    {
        Coupon::factory()->create(['code'=>'ONE1','max_redemptions_per_school'=>1]);
        app(CouponService::class)->apply('ONE1', $this->school, $this->subscription, 1000);
        $this->expectException(RuntimeException::class);
        app(CouponService::class)->validate('ONE1', $this->school);
    }
    /** @test */ public function percentage_discount_is_correct(): void { Coupon::factory()->create(['code'=>'PCT20','value'=>20]); $this->assertEquals(200.0, app(CouponService::class)->apply('PCT20', $this->school, $this->subscription, 1000)->discount_applied); }
    /** @test */ public function fixed_discount_never_exceeds_the_base(): void { Coupon::factory()->create(['code'=>'FIX500','discount_type'=>'fixed_amount','value'=>500]); $this->assertEquals(300.0, app(CouponService::class)->apply('FIX500', $this->school, $this->subscription, 300)->discount_applied); }
}
