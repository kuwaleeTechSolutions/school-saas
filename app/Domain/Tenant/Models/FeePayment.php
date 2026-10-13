<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
class FeePayment extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_invoice_id','student_id','transaction_reference','amount','method','status','gateway','gateway_response','recorded_by','paid_at'];
    protected function casts(): array { return ['amount'=>'decimal:2','gateway_response'=>'array','paid_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn($p) => $p->ulid ??= (string) Str::ulid()); }
    public function invoice(): BelongsTo { return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function receipt(): HasOne { return $this->hasOne(PaymentReceipt::class); }
    public function refunds(): HasMany { return $this->hasMany(FeeRefund::class); }
    public function totalRefunded(): float { return (float) $this->refunds()->where('status','processed')->sum('amount'); }
}
