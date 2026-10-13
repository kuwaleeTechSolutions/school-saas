<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentScholarship extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','student_id','scholarship_id','academic_session_id','approved_by'];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function scholarship(): BelongsTo { return $this->belongsTo(Scholarship::class); }
}
