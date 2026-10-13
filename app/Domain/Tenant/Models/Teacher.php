<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Teacher extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','user_id','employee_code','qualification','specialization','joining_date','employment_type','status'];
    protected function casts(): array { return ['joining_date'=>'date']; }
    protected static function booted(): void { static::creating(fn($t) => $t->ulid ??= (string) Str::ulid()); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
    public function subjectAssignments(): HasMany { return $this->hasMany(TeacherSubjectAssignment::class); }
    public function classesAsClassTeacher(): HasMany { return $this->hasMany(Section::class, 'class_teacher_id'); }
}
