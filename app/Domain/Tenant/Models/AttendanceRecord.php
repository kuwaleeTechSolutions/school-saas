<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AttendanceRecord extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','attendance_session_id','student_id','status','remarks'];
    public function session(): BelongsTo { return $this->belongsTo(AttendanceSession::class, 'attendance_session_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function corrections(): HasMany { return $this->hasMany(AttendanceCorrection::class)->orderBy('created_at'); }
}
