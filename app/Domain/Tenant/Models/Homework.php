<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Homework extends Model {
    use BelongsToSchool, SoftDeletes;
    protected $table = 'homework';
    protected $fillable = ['school_id','class_id','section_id','subject_id','teacher_id','title','instructions','attachment_path','assigned_date','due_date'];
    protected function casts(): array { return ['assigned_date'=>'date','due_date'=>'date']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function submissions(): HasMany { return $this->hasMany(HomeworkSubmission::class); }
    public function isOverdue(): bool { return $this->due_date->isPast(); }
}
