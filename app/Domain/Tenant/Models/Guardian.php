<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Guardian extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','user_id','name','phone','email','occupation','address'];
    protected static function booted(): void { static::creating(fn($g) => $g->ulid ??= (string) Str::ulid()); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
    public function students(): BelongsToMany { return $this->belongsToMany(Student::class, 'student_guardians', 'guardian_id', 'student_id')->withPivot(['relation','is_primary_contact'])->withTimestamps(); }
}
