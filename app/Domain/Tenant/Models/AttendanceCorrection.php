<?php
namespace App\Domain\Tenant\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceCorrection extends Model {
    public $timestamps = false;
    protected $fillable = ['school_id','attendance_record_id','old_status','new_status','corrected_by','reason','created_at'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
    public function record(): BelongsTo { return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id'); }
}
