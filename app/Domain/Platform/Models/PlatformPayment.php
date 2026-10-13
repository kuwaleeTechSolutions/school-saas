<?php
namespace App\Domain\Platform\Models;
use App\Domain\Tenant\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class PlatformPayment extends Model
{
    protected $fillable = ['platform_invoice_id','school_id','method','gateway','gateway_reference','gateway_response','amount','status','recorded_by','paid_at'];
    protected function casts(): array { return ['gateway_response'=>'array','amount'=>'decimal:2','paid_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn(self $p) => $p->ulid ??= (string) Str::ulid()); }
    public function invoice(): BelongsTo { return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
