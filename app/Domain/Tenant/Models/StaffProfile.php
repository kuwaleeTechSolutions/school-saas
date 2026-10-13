<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StaffProfile extends Model {
    use BelongsToSchool, HasFactory, SoftDeletes;
    protected $fillable = ['school_id','user_id','employee_code','designation','department','joining_date','monthly_gross_salary','annual_leave_quota','status'];
    protected function casts(): array { return ['joining_date'=>'date','monthly_gross_salary'=>'decimal:2']; }
    protected static function booted(): void { static::creating(fn($s) => $s->ulid ??= (string) Str::ulid()); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function payslips(): HasMany { return $this->hasMany(Payslip::class); }
    public function approvedLeaveDaysInMonth(int $year, int $month): int {
        return $this->leaveRequests()->where('status', 'approved')->get()->sum(function (LeaveRequest $leave) use ($year, $month) {
            $periodStart = \Illuminate\Support\Carbon::create($year, $month, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
            $overlapStart = $leave->starts_on->max($periodStart);
            $overlapEnd = $leave->ends_on->min($periodEnd);
            return $overlapStart->lte($overlapEnd) ? $overlapStart->diffInDays($overlapEnd) + 1 : 0;
        });
    }
    protected static function newFactory()
    {
        return \Database\Factories\StaffProfileFactory::new();
    }
}
