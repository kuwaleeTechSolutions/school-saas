<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FeeStructureItem extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_structure_id','fee_component_id','annual_amount'];
    protected function casts(): array { return ['annual_amount'=>'decimal:2']; }
    public function feeStructure(): BelongsTo { return $this->belongsTo(FeeStructure::class); }
    public function feeComponent(): BelongsTo { return $this->belongsTo(FeeComponent::class); }
}
