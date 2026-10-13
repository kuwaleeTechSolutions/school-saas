<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SchoolDocument extends Model {
    use BelongsToSchool, SoftDeletes;
    protected $fillable = ['school_id','title','category','file_path','original_name','audience_type','audience_role','uploaded_by'];
    protected static function booted(): void { static::creating(fn($d) => $d->ulid ??= (string) Str::ulid()); }
}
