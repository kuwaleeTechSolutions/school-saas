<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Exam extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','exam_type_id','academic_session_id','class_id','name','starts_on','ends_on','status'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date']; }
    protected static function booted(): void { static::creating(fn($e) => $e->ulid ??= (string) Str::ulid()); }
    public function examType(): BelongsTo { return $this->belongsTo(ExamType::class); }
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function examSubjects(): HasMany { return $this->hasMany(ExamSubject::class); }
    public function reportCards(): HasMany { return $this->hasMany(ReportCard::class); }
    public function isFullyPublished(): bool { return $this->examSubjects()->count() > 0 && $this->examSubjects()->where('status','!=','published')->doesntExist(); }
}
