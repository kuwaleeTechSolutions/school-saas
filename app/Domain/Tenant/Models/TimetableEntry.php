<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TimetableEntry extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','timetable_id','day_of_week','timetable_period_id','subject_id','teacher_id','room_id'];
    public function timetable(): BelongsTo { return $this->belongsTo(Timetable::class); }
    public function period(): BelongsTo { return $this->belongsTo(TimetablePeriod::class, 'timetable_period_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
}
