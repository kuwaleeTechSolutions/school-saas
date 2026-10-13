<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class LibraryFineRule extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','per_day_amount','max_amount','is_active'];
    protected function casts(): array { return ['per_day_amount'=>'decimal:2','max_amount'=>'decimal:2','is_active'=>'boolean']; }
}
