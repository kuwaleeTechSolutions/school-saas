<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PlatformInvoiceLineItem extends Model
{
    protected $fillable = ['platform_invoice_id','description','line_type','amount'];
    protected function casts(): array { return ['amount'=>'decimal:2']; }
    public function invoice(): BelongsTo { return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id'); }
}
