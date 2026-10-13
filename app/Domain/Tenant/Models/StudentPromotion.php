<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentPromotion extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','student_id','from_academic_session_id','from_class_id','from_section_id','to_academic_session_id','to_class_id','to_section_id','decision','promoted_by','batch_ulid'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function fromClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'from_class_id'); }
    public function toClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'to_class_id'); }
}
