<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PlanPrice extends Model {
    protected $fillable = ['plan_id','billing_cycle','price','setup_fee','discount_percent','currency','tax_applicable'];
    protected function casts(): array { return ['price'=>'decimal:2','setup_fee'=>'decimal:2','discount_percent'=>'decimal:2','tax_applicable'=>'boolean']; }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
}
