<?php
namespace App\Domain\Tenant\Models;
use App\Domain\Shared\Tenancy\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payslip extends Model {
    use BelongsToSchool;
    protected $fillable = ['school_id','payroll_run_id','staff_profile_id','gross_salary','unpaid_leave_days','leave_deduction','other_deductions','net_salary','pdf_path','pdf_status'];
    protected function casts(): array { return ['gross_salary'=>'decimal:2','leave_deduction'=>'decimal:2','other_deductions'=>'decimal:2','net_salary'=>'decimal:2']; }
    protected static function booted(): void { static::creating(fn($p) => $p->ulid ??= (string) Str::ulid()); }
    public function payrollRun(): BelongsTo { return $this->belongsTo(PayrollRun::class); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
}
