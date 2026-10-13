<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\PayrollRun;
use App\Domain\Tenant\Models\Payslip;
use App\Domain\Tenant\Models\StaffProfile;
use App\Jobs\GeneratePayslipPdf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    public function __construct(protected AuditService $audit) {}

    public function generate(int $year, int $month, User $actor): PayrollRun
    {
        $existing = PayrollRun::where('year', $year)->where('month', $month)->first();
        if ($existing) throw new RuntimeException("A payroll run for {$month}/{$year} already exists.");

        return DB::transaction(function () use ($year, $month, $actor) {
            $run = PayrollRun::create(['year' => $year, 'month' => $month, 'status' => 'draft', 'generated_by' => $actor->id]);
            $staffMembers = StaffProfile::where('status', 'active')->get();
            foreach ($staffMembers as $staff) { $this->generatePayslipFor($run, $staff); }
            $this->audit->logSchool($actor->id, 'payroll.generated', PayrollRun::class, $run->id, null, ['period' => "{$month}/{$year}", 'staff_count' => $staffMembers->count()]);
            return $run->fresh('payslips');
        });
    }

    protected function generatePayslipFor(PayrollRun $run, StaffProfile $staff): Payslip
    {
        $gross = (float) $staff->monthly_gross_salary;
        $monthlyQuota = $staff->annual_leave_quota / 12;
        $takenDays = $staff->approvedLeaveDaysInMonth($run->year, $run->month);
        $unpaidDays = max(0, $takenDays - $monthlyQuota);
        $daysInMonth = \Illuminate\Support\Carbon::create($run->year, $run->month, 1)->daysInMonth;
        $perDayRate = $daysInMonth > 0 ? $gross / $daysInMonth : 0;
        $leaveDeduction = round($perDayRate * $unpaidDays, 2);
        $net = max(0, $gross - $leaveDeduction);

        return Payslip::create([
            'payroll_run_id' => $run->id, 'staff_profile_id' => $staff->id, 'gross_salary' => $gross,
            'unpaid_leave_days' => (int) ceil($unpaidDays), 'leave_deduction' => $leaveDeduction,
            'other_deductions' => 0, 'net_salary' => $net, 'pdf_status' => 'pending',
        ]);
    }

    public function finalize(PayrollRun $run, User $actor): PayrollRun
    {
        if ($run->status === 'finalized') throw new RuntimeException('This payroll run has already been finalized.');
        $run->update(['status' => 'finalized', 'finalized_at' => now()]);
        foreach ($run->payslips as $payslip) {
            $payslip->update(['pdf_status' => 'queued']);
            GeneratePayslipPdf::dispatch($payslip->id);
        }
        $this->audit->logSchool($actor->id, 'payroll.finalized', PayrollRun::class, $run->id);
        return $run->fresh();
    }
}
