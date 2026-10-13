<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class SchoolClass extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $table = 'classes';
    protected $fillable = ['school_id','academic_session_id','name','sort_order'];
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function sections(): HasMany { return $this->hasMany(Section::class, 'class_id'); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'class_subjects', 'class_id', 'subject_id'); }
    public function enrollments(): HasMany { return $this->hasMany(StudentEnrollment::class, 'class_id'); }
    protected static function newFactory()
    {
        return \Database\Factories\SchoolClassFactory::new();
    }
}
