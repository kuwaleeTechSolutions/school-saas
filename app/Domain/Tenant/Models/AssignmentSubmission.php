<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssignmentSubmission extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','assignment_id','student_id','attachment_path','student_note','submitted_at','status','marks_obtained','teacher_comment'];
    protected function casts(): array { return ['submitted_at'=>'datetime','marks_obtained'=>'decimal:2']; }
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
