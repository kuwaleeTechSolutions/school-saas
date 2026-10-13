<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
class LateFeeRule extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','grace_days','calculation_type','value','max_amount','is_active'];
    protected function casts(): array { return ['value'=>'decimal:2','max_amount'=>'decimal:2','is_active'=>'boolean']; }
    public function computeFor(int $daysOverdue): float
    {
        $billableDays = max(0, $daysOverdue - $this->grace_days);
        if ($billableDays <= 0) return 0.0;
        $amount = match ($this->calculation_type) {
            'fixed' => (float) $this->value,
            'fixed_per_day' => (float) $this->value * $billableDays,
            'percentage_per_day' => null,
            default => 0.0,
        };
        if ($amount === null) return 0.0;
        return $this->max_amount !== null ? min($amount, (float) $this->max_amount) : $amount;
    }
}
