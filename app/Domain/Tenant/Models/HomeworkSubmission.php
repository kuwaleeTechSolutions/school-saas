<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HomeworkSubmission extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','homework_id','student_id','attachment_path','student_note','submitted_at','status','grade_points','teacher_comment'];
    protected function casts(): array { return ['submitted_at'=>'datetime','grade_points'=>'decimal:2']; }
    public function homework(): BelongsTo { return $this->belongsTo(Homework::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
