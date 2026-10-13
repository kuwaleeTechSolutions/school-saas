<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class PaymentReceipt extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_payment_id','receipt_number','qr_token','pdf_path','pdf_status'];
    protected static function booted(): void { static::creating(fn($r) => $r->qr_token ??= Str::random(32)); }
    public function payment(): BelongsTo { return $this->belongsTo(FeePayment::class, 'fee_payment_id'); }
}
