<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class FeeStructure extends Model {
    use BelongsToSchool, SoftDeletes;
    protected $fillable = ['school_id','academic_session_id','name','class_id','section_id','student_category','requires_transport','requires_hostel','is_active'];
    protected function casts(): array { return ['requires_transport'=>'boolean','requires_hostel'=>'boolean','is_active'=>'boolean']; }
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function items(): HasMany { return $this->hasMany(FeeStructureItem::class); }
    public function installmentPlans(): HasMany { return $this->hasMany(FeeInstallmentPlan::class); }
    public function totalAnnualAmount(): float { return (float) $this->items()->sum('annual_amount'); }
    public function specificityScore(): int
    {
        return ($this->class_id ? 2 : 0) + ($this->section_id ? 2 : 0) + ($this->student_category ? 1 : 0) + ($this->requires_transport ? 1 : 0) + ($this->requires_hostel ? 1 : 0);
    }
}
