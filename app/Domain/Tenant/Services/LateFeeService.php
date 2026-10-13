<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\LateFeeRule;
use Illuminate\Support\Carbon;

class LateFeeService
{
    /**
     * Computes the late fee that SHOULD apply as of the given date — purely
     * a function of ($invoice->due_date, $asOf), never real wall-clock
     * now(), so this is reliably testable against historical/future dates.
     */
    public function computeLateFee(FeeInvoice $invoice, ?Carbon $asOf = null): float
    {
        $asOf = $asOf ?? now();

        if (! in_array($invoice->status, ['unpaid', 'partially_paid', 'overdue'], true)) {
            return 0.0;
        }

        if ($asOf->lte($invoice->due_date)) {
            return 0.0;
        }

        $rule = LateFeeRule::where('school_id', $invoice->school_id)->where('is_active', true)->first();
        if (! $rule) return 0.0;

        $daysOverdue = (int) $invoice->due_date->diffInDays($asOf, false);
        $billableDays = $daysOverdue - $rule->grace_days;
        if ($billableDays <= 0) return 0.0;

        $amount = match ($rule->calculation_type) {
            'fixed' => (float) $rule->value,
            'fixed_per_day' => (float) $rule->value * $billableDays,
            'percentage_per_day' => round(((float) $invoice->subtotal_amount) * ((float) $rule->value / 100) * $billableDays, 2),
            default => 0.0,
        };

        return $rule->max_amount !== null ? min($amount, (float) $rule->max_amount) : $amount;
    }
}
