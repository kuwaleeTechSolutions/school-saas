<?php
namespace App\Domain\Tenant\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MarkApprovalHistory extends Model {
    public $timestamps = false;
    protected $fillable = ['school_id','exam_subject_id','from_status','to_status','actor_id','notes','created_at'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
    public function examSubject(): BelongsTo { return $this->belongsTo(ExamSubject::class); }
}
