<?php
namespace App\Domain\Platform\Services;
use App\Domain\Platform\Models\Coupon;
use App\Domain\Platform\Models\CouponRedemption;
use App\Domain\Platform\Models\Subscription;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\School;
use RuntimeException;
/** validate() is side-effect free (safe for live UI feedback); apply() writes the redemption row. */
class CouponService
{
    public function __construct(protected AuditService $audit) {}
    public function validate(string $code, School $school): Coupon
    {
        $coupon = Coupon::where('code', strtoupper($code))->where('is_active', true)->first();
        if (! $coupon) throw new RuntimeException('This coupon code is not valid.');
        if (! $coupon->isWithinValidityWindow()) throw new RuntimeException('This coupon has expired or is not yet active.');
        if (! $coupon->hasRedemptionsRemaining()) throw new RuntimeException('This coupon has reached its maximum number of uses.');
        if ($coupon->redemptions()->where('school_id', $school->id)->count() >= $coupon->max_redemptions_per_school) {
            throw new RuntimeException('Your school has already used this coupon the maximum allowed number of times.');
        }
        return $coupon;
    }
    public function apply(string $code, School $school, Subscription $subscription, float $baseAmount): CouponRedemption
    {
        $coupon = $this->validate($code, $school);
        $discount = $coupon->discountFor($baseAmount);
        $redemption = CouponRedemption::create(['coupon_id'=>$coupon->id,'school_id'=>$school->id,'subscription_id'=>$subscription->id,'discount_applied'=>$discount,'redeemed_at'=>now()]);
        $this->audit->logPlatform(null, 'coupon.redeemed', CouponRedemption::class, $redemption->id, null, ['coupon_code'=>$coupon->code,'school_id'=>$school->id,'discount'=>$discount]);
        return $redemption;
    }
}
