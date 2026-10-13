<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Mark extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','exam_subject_id','student_id','marks_obtained','is_absent','entered_by'];
    protected function casts(): array { return ['marks_obtained'=>'decimal:2','is_absent'=>'boolean']; }
    public function examSubject(): BelongsTo { return $this->belongsTo(ExamSubject::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function percent(): ?float
    {
        if ($this->is_absent || $this->marks_obtained === null) return null;
        $maxMarks = (float) $this->examSubject->max_marks;
        return $maxMarks > 0 ? round(((float) $this->marks_obtained / $maxMarks) * 100, 2) : null;
    }
    public function isPassing(): bool { return ! $this->is_absent && $this->marks_obtained !== null && $this->marks_obtained >= $this->examSubject->pass_marks; }
}
