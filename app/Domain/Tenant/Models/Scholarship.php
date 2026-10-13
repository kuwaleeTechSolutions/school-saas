<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class Scholarship extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','name','discount_type','value','is_active'];
    protected function casts(): array { return ['value'=>'decimal:2','is_active'=>'boolean']; }
    public function discountFor(float $baseAmount): float
    {
        return $this->discount_type === 'percentage' ? round($baseAmount * ((float) $this->value / 100), 2) : min((float) $this->value, $baseAmount);
    }
}
