<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class ExamSubject extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','exam_id','subject_id','max_marks','pass_marks','status'];
    protected function casts(): array { return ['max_marks'=>'decimal:2','pass_marks'=>'decimal:2']; }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function schedule(): HasOne { return $this->hasOne(ExamSchedule::class); }
    public function marks(): HasMany { return $this->hasMany(Mark::class); }
    public function approvalHistories(): HasMany { return $this->hasMany(MarkApprovalHistory::class)->orderBy('created_at'); }
}
