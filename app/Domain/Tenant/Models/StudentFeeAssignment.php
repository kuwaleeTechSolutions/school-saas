<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentFeeAssignment extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','student_id','academic_session_id','fee_structure_id','fee_installment_plan_id'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function feeStructure(): BelongsTo { return $this->belongsTo(FeeStructure::class); }
    public function installmentPlan(): BelongsTo { return $this->belongsTo(FeeInstallmentPlan::class, 'fee_installment_plan_id'); }
}
