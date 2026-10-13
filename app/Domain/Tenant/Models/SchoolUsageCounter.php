<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class SchoolUsageCounter extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','metric','current_value','period_start','period_end'];
    protected function casts(): array { return ['period_start'=>'datetime','period_end'=>'datetime']; }
}
