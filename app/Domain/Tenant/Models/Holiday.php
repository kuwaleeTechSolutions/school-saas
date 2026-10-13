<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class Holiday extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','holiday_date','title'];
    protected function casts(): array { return ['holiday_date'=>'date']; }
}
