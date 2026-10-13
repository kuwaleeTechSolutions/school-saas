<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['ulid','code','description','discount_type','value','max_redemptions','max_redemptions_per_school','valid_from','valid_until','is_active','created_by'];
    protected function casts(): array { return ['value'=>'decimal:2','valid_from'=>'date','valid_until'=>'date','is_active'=>'boolean']; }
    protected static function booted(): void {
        static::creating(function (self $c) { $c->ulid ??= (string) Str::ulid(); $c->code = strtoupper($c->code); });
    }
    public function redemptions(): HasMany { return $this->hasMany(CouponRedemption::class); }
    public function isWithinValidityWindow(?Carbon $asOf = null): bool {
        $asOf = $asOf ?? now();
        if ($this->valid_from && $asOf->lt($this->valid_from)) return false;
        if ($this->valid_until && $asOf->gt($this->valid_until->copy()->endOfDay())) return false;
        return true;
    }
    public function hasRedemptionsRemaining(): bool {
        return $this->max_redemptions === null || $this->redemptions()->count() < $this->max_redemptions;
    }
    public function discountFor(float $baseAmount): float {
        return $this->discount_type === 'percentage' ? round($baseAmount * ((float) $this->value / 100), 2) : min((float) $this->value, $baseAmount);
    }
    protected static function newFactory()
    {
        return \Database\Factories\CouponFactory::new();
    }
}
