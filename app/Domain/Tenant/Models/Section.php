<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Section extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','class_id','name','capacity','class_teacher_id','sort_order'];
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function classTeacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'class_teacher_id'); }
    public function enrollments(): HasMany { return $this->hasMany(StudentEnrollment::class, 'section_id'); }
    protected static function newFactory()
    {
        return \Database\Factories\SectionFactory::new();
    }
}
