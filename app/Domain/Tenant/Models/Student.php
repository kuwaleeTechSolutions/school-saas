<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Student extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','user_id','admission_number','first_name','middle_name','last_name','gender','date_of_birth','photo','blood_group','nationality','religion','category','phone','email','address','city','district','state','pincode','admission_date','house_id','previous_school','transfer_details','allergies','medical_notes','emergency_contact_name','emergency_contact_phone','status'];
    protected function casts(): array { return ['date_of_birth'=>'date','admission_date'=>'date']; }
    protected static function booted(): void { static::creating(fn($s) => $s->ulid ??= (string) Str::ulid()); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
    public function house(): BelongsTo { return $this->belongsTo(House::class); }
    public function guardians(): BelongsToMany { return $this->belongsToMany(Guardian::class, 'student_guardians', 'student_id', 'guardian_id')->withPivot(['relation','is_primary_contact'])->withTimestamps(); }
    public function documents(): HasMany { return $this->hasMany(StudentDocument::class); }
    public function enrollments(): HasMany { return $this->hasMany(StudentEnrollment::class); }
    public function currentEnrollment(): HasOne { return $this->hasOne(StudentEnrollment::class)->whereHas('academicSession', fn($q) => $q->where('is_current', true)); }
    public function fullName(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter(fn ($part) => filled($part))
        ->implode(' ');
    }
    protected static function newFactory()
    {
        return \Database\Factories\StudentFactory::new();
    }
}
