<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class FeeRefund extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_payment_id','amount','reason','status','requested_by','approved_by','rejection_reason','processed_at'];
    protected function casts(): array { return ['amount'=>'decimal:2','processed_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn($r) => $r->ulid ??= (string) Str::ulid()); }
    public function payment(): BelongsTo { return $this->belongsTo(FeePayment::class, 'fee_payment_id'); }
}
