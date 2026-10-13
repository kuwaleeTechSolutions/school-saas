<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class AdmissionEnquiry extends Model {
    use BelongsToSchool, HasFactory;
    protected $fillable = ['school_id','child_name','guardian_name','phone','email','interested_class','notes','status'];
    protected static function booted(): void { static::creating(fn($e) => $e->ulid ??= (string) Str::ulid()); }
    public function applications(): HasMany { return $this->hasMany(AdmissionApplication::class); }
}
