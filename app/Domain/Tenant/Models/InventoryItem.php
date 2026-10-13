<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','name','category','unit','reorder_level'];
    public function transactions(): HasMany { return $this->hasMany(InventoryTransaction::class); }
    public function currentStock(): int { return (int) $this->transactions()->sum('quantity'); }
    public function isBelowReorderLevel(): bool { return $this->currentStock() < $this->reorder_level; }
}
