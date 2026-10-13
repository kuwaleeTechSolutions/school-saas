<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AttendanceSession extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','academic_session_id','class_id','section_id','attendance_date','period_number','taken_by','locked_at'];
    protected function casts(): array { return ['attendance_date'=>'date','locked_at'=>'datetime','period_number'=>'integer']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function records(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function isDaily(): bool { return $this->period_number === 0; }
}
