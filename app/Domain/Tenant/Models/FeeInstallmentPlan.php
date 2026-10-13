<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class FeeInstallmentPlan extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_structure_id','name','frequency'];
    public function feeStructure(): BelongsTo { return $this->belongsTo(FeeStructure::class); }
    public function installments(): HasMany { return $this->hasMany(FeeInstallment::class)->orderBy('installment_number'); }
}
