<?php
namespace App\Domain\Platform\Models;
use App\Domain\Tenant\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** Deliberately NOT tenant-scoped: Super Admin queries across all schools. Tenant screens filter by school_id explicitly. */
class PlatformInvoice extends Model
{
    use SoftDeletes;
    protected $fillable = ['school_id','subscription_id','invoice_number','period_starts_on','period_ends_on','due_date','subtotal_amount','discount_amount','tax_amount','total_amount','paid_amount','balance_amount','coupon_redemption_id','status'];
    protected function casts(): array {
        return ['period_starts_on'=>'date','period_ends_on'=>'date','due_date'=>'date','subtotal_amount'=>'decimal:2','discount_amount'=>'decimal:2','tax_amount'=>'decimal:2','total_amount'=>'decimal:2','paid_amount'=>'decimal:2','balance_amount'=>'decimal:2'];
    }
    protected static function booted(): void { static::creating(fn(self $i) => $i->ulid ??= (string) Str::ulid()); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function couponRedemption(): BelongsTo { return $this->belongsTo(CouponRedemption::class); }
    public function lineItems(): HasMany { return $this->hasMany(PlatformInvoiceLineItem::class); }
    public function payments(): HasMany { return $this->hasMany(PlatformPayment::class); }
    public function isOverdue(): bool { return in_array($this->status, ['unpaid','partially_paid'], true) && $this->due_date->isPast(); }
}
