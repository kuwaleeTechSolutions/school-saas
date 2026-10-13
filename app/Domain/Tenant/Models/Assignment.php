<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Assignment extends Model {
    use BelongsToSchool, SoftDeletes;
    protected $fillable = ['school_id','class_id','section_id','subject_id','teacher_id','title','instructions','attachment_path','max_marks','assigned_date','due_date'];
    protected function casts(): array { return ['assigned_date'=>'date','due_date'=>'date','max_marks'=>'decimal:2']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
}
