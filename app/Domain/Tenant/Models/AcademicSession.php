<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademicSession extends Model {
    use BelongsToSchool, HasFactory;
    protected $fillable = ['school_id','name','starts_on','ends_on','is_current'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date','is_current'=>'boolean']; }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function enrollments(): HasMany { return $this->hasMany(StudentEnrollment::class); }
    public function markAsCurrent(): void
    {
        static::where('school_id', $this->school_id)->where('id', '!=', $this->id)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
        $this->school->update(['current_academic_session_id' => $this->id]);
    }
    protected static function newFactory()
    {
        return \Database\Factories\AcademicSessionFactory::new();
    }
}
