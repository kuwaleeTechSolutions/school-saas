<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','label','min_percent','max_percent','remark'];
    protected function casts(): array { return ['min_percent'=>'decimal:2','max_percent'=>'decimal:2']; }
    public static function forPercent(float $percent, int $schoolId): ?self
    {
        return static::where('school_id', $schoolId)->where('min_percent', '<=', $percent)->where('max_percent', '>=', $percent)->first();
    }
}
