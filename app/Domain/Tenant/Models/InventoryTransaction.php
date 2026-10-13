<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','inventory_item_id','type','quantity','reference','recorded_by','transacted_at'];
    protected function casts(): array { return ['transacted_at'=>'datetime']; }
    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
}
