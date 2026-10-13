<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ExamSchedule extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','exam_subject_id','exam_date','starts_at','ends_at','room_id'];
    protected function casts(): array { return ['exam_date'=>'date']; }
    public function examSubject(): BelongsTo { return $this->belongsTo(ExamSubject::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
}
