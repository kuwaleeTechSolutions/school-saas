<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvoiceLineItem extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_invoice_id','fee_component_id','description','line_type','amount'];
    protected function casts(): array { return ['amount'=>'decimal:2']; }
    public function invoice(): BelongsTo { return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id'); }
    public function feeComponent(): BelongsTo { return $this->belongsTo(FeeComponent::class); }
}
