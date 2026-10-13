<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\InvoiceLineItem;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentFeeAssignment;
use App\Domain\Tenant\Models\StudentScholarship;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceGenerationService
{
    public function __construct(protected AuditService $audit, protected LateFeeService $lateFeeService) {}

    public function generateForInstallment(Student $student, int $feeInstallmentId, User $actor): FeeInvoice
    {
        $assignment = StudentFeeAssignment::where('student_id', $student->id)
            ->whereHas('installmentPlan.installments', fn($q) => $q->where('id', $feeInstallmentId))->first();
        if (! $assignment) {
            throw new RuntimeException('This student is not assigned to a fee plan containing this installment.');
        }
        $installment = $assignment->installmentPlan->installments()->findOrFail($feeInstallmentId);
        $existing = FeeInvoice::where('student_id', $student->id)->where('fee_installment_id', $feeInstallmentId)->first();
        if ($existing) return $existing;

        return DB::transaction(function () use ($student, $assignment, $installment, $actor) {
            $structure = $assignment->feeStructure()->with('items.feeComponent')->first();
            $totalAnnual = $structure->totalAnnualAmount();
            $installmentShare = round($totalAnnual * ((float) $installment->percentage_of_total / 100), 2);

            $invoice = FeeInvoice::create([
                'student_id' => $student->id, 'academic_session_id' => $assignment->academic_session_id,
                'fee_installment_id' => $installment->id, 'invoice_number' => $this->generateInvoiceNumber($student),
                'due_date' => $installment->due_date, 'subtotal_amount' => $installmentShare, 'discount_amount' => 0,
                'late_fee_amount' => 0, 'tax_amount' => 0, 'total_amount' => $installmentShare,
                'paid_amount' => 0, 'balance_amount' => $installmentShare, 'status' => 'unpaid',
            ]);

            foreach ($structure->items as $item) {
                InvoiceLineItem::create([
                    'fee_invoice_id' => $invoice->id, 'fee_component_id' => $item->fee_component_id,
                    'description' => $item->feeComponent->name, 'line_type' => 'component',
                    'amount' => round((float) $item->annual_amount * ((float) $installment->percentage_of_total / 100), 2),
                ]);
            }

            $this->applyScholarshipDiscount($invoice, $student, $assignment->academic_session_id);

            $this->audit->logSchool($actor->id, 'fee_invoice.generated', FeeInvoice::class, $invoice->id, null, [
                'student_id' => $student->id, 'installment' => $installment->label, 'amount' => $installmentShare,
            ]);
            return $invoice->fresh('lineItems');
        });
    }

    public function generateForInstallmentBulk(int $feeInstallmentId, User $actor): array
    {
        $assignments = StudentFeeAssignment::whereHas('installmentPlan.installments', fn($q) => $q->where('id', $feeInstallmentId))->get();
        $invoices = [];
        foreach ($assignments as $assignment) {
            $student = Student::find($assignment->student_id);
            if ($student) $invoices[] = $this->generateForInstallment($student, $feeInstallmentId, $actor);
        }
        return $invoices;
    }

    protected function applyScholarshipDiscount(FeeInvoice $invoice, Student $student, int $academicSessionId): void
    {
        $studentScholarships = StudentScholarship::where('student_id', $student->id)
            ->where('academic_session_id', $academicSessionId)->with('scholarship')->get();
        if ($studentScholarships->isEmpty()) return;

        $totalDiscount = 0.0;
        foreach ($studentScholarships as $studentScholarship) {
            $scholarship = $studentScholarship->scholarship;
            $discount = $scholarship->discountFor((float) $invoice->subtotal_amount);
            $totalDiscount += $discount;
            InvoiceLineItem::create([
                'fee_invoice_id' => $invoice->id, 'description' => "Scholarship: {$scholarship->name}",
                'line_type' => 'discount', 'amount' => -$discount,
            ]);
        }
        $invoice->discount_amount = $totalDiscount;
        $invoice->total_amount = max(0, (float) $invoice->subtotal_amount - $totalDiscount);
        $invoice->balance_amount = $invoice->total_amount - (float) $invoice->paid_amount;
        $invoice->save();
    }

    public function refreshLateFee(FeeInvoice $invoice): FeeInvoice
    {
        $newLateFee = $this->lateFeeService->computeLateFee($invoice);
        return DB::transaction(function () use ($invoice, $newLateFee) {
            $invoice->lineItems()->where('line_type', 'late_fee')->delete();
            if ($newLateFee > 0) {
                InvoiceLineItem::create(['fee_invoice_id' => $invoice->id, 'description' => 'Late Fee', 'line_type' => 'late_fee', 'amount' => $newLateFee]);
            }
            $invoice->late_fee_amount = $newLateFee;
            $invoice->total_amount = max(0, (float) $invoice->subtotal_amount - (float) $invoice->discount_amount + $newLateFee + (float) $invoice->tax_amount);
            $invoice->balance_amount = $invoice->total_amount - (float) $invoice->paid_amount;
            if ($invoice->due_date->isPast() && in_array($invoice->status, ['unpaid', 'partially_paid'], true)) {
                $invoice->status = $invoice->paid_amount > 0 ? 'partially_paid' : 'overdue';
            }
            $invoice->save();
            return $invoice->fresh('lineItems');
        });
    }

    protected function generateInvoiceNumber(Student $student): string
    {
        $year = now()->format('y');
        $sequence = FeeInvoice::where('school_id', $student->school_id)->count() + 1;
        return "INV{$year}-" . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
