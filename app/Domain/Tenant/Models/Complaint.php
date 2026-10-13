<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','subject','description','category','status','raised_by','assigned_to','resolution_notes','resolved_at'];
    protected function casts(): array { return ['resolved_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn($c) => $c->ulid ??= (string) Str::ulid()); }
}
