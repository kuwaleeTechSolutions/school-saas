<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\FeePayment;
use Illuminate\Support\Carbon;

class FinancialReportService
{
    public function collectionsBetween(Carbon $from, Carbon $to): float
    {
        return (float) FeePayment::where('status', 'success')->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])->sum('amount');
    }
    public function outstandingTotal(): float
    {
        return (float) FeeInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->sum('balance_amount');
    }
    public function overdueInvoicesCount(): int
    {
        return FeeInvoice::where('status', 'overdue')->count();
    }
    public function collectionsByMethod(Carbon $from, Carbon $to): array
    {
        return FeePayment::where('status', 'success')->whereBetween('paid_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('method, SUM(amount) as total')->groupBy('method')->pluck('total', 'method')->map(fn($v) => (float) $v)->all();
    }
    public function outstandingInvoices()
    {
        return FeeInvoice::whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->with('student')->orderBy('due_date')->get();
    }
}
