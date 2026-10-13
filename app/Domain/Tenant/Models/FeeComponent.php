<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class FeeComponent extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','category','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
