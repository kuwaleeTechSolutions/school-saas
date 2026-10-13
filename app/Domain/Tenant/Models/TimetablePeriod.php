<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class TimetablePeriod extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','sort_order','starts_at','ends_at','is_teaching_period'];
    protected function casts(): array { return ['is_teaching_period'=>'boolean']; }
}
