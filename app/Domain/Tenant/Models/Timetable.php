<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Timetable extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','academic_session_id','class_id','section_id','status','published_by','published_at'];
    protected function casts(): array { return ['published_at'=>'datetime']; }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function entries(): HasMany { return $this->hasMany(TimetableEntry::class); }
}
