<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FeeInstallment extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','fee_installment_plan_id','installment_number','label','percentage_of_total','due_date'];
    protected function casts(): array { return ['percentage_of_total'=>'decimal:2','due_date'=>'date']; }
    public function plan(): BelongsTo { return $this->belongsTo(FeeInstallmentPlan::class, 'fee_installment_plan_id'); }
}
