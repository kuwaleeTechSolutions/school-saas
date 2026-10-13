<?php
namespace App\Domain\Platform\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
class Admin extends Authenticatable {
    use HasFactory, Notifiable, SoftDeletes;
    protected $fillable = ['ulid','name','email','password','avatar','status'];
    protected $hidden = ['password','remember_token'];
    protected static function booted(): void { static::creating(fn($a) => $a->ulid ??= (string) Str::ulid()); }
    protected function casts(): array { return ['email_verified_at'=>'datetime','last_login_at'=>'datetime','password'=>'hashed']; }
}
