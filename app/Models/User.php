<?php
namespace App\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
    use BelongsToSchool, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    protected $guard_name = 'web';
    protected $fillable = ['ulid','school_id','name','email','phone','password','avatar','status'];
    protected $hidden = ['password','remember_token'];
    protected static function booted(): void { static::creating(fn($u) => $u->ulid ??= (string) Str::ulid()); }
    protected function casts(): array { return ['email_verified_at'=>'datetime','phone_verified_at'=>'datetime','last_login_at'=>'datetime','password'=>'hashed']; }
}
