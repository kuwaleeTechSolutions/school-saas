<?php
namespace App\Domain\Tenant\Reports\Definitions;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Reports\ReportDefinition;
use Illuminate\Support\Collection;
class FeeOutstandingReport implements ReportDefinition
{
    public function key(): string { return 'fees_outstanding'; }
    public function title(): string { return 'Outstanding Fees'; }
    public function permission(): string { return 'fees.export'; }
    public function filters(): array { return ['due_before' => ['label' => 'Due on or before', 'type' => 'date']]; }
    public function columns(): array { return ['invoice_number' => 'Invoice #', 'student' => 'Student', 'due_date' => 'Due Date', 'total' => 'Total', 'balance' => 'Balance', 'status' => 'Status']; }
    public function rules(array $input = []): array { return ['due_before' => ['nullable', 'date']]; }
    public function rows(array $filters, int $limit): Collection
    {
        return FeeInvoice::query()->with('student')->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->when($filters['due_before'] ?? null, fn ($q, $v) => $q->whereDate('due_date', '<=', $v))
            ->orderBy('due_date')->limit($limit)->get()
            ->map(fn ($i) => ['invoice_number' => $i->invoice_number, 'student' => $i->student->fullName(), 'due_date' => $i->due_date->toDateString(), 'total' => (float) $i->total_amount, 'balance' => (float) $i->balance_amount, 'status' => $i->status]);
    }
}
