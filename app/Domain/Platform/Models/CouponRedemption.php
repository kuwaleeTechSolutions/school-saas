<?php
namespace App\Domain\Platform\Models;
use App\Domain\Tenant\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CouponRedemption extends Model
{
    protected $fillable = ['coupon_id','school_id','subscription_id','discount_applied','redeemed_at'];
    protected function casts(): array { return ['discount_applied'=>'decimal:2','redeemed_at'=>'datetime']; }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
}
