<?php

namespace App\Domain\Tenant\Services;

use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\FeePayment;
use App\Domain\Tenant\Models\PaymentReceipt;
use App\Events\FeePaymentRecorded;
use App\Jobs\GeneratePaymentReceiptPdf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * STAGE 6 OVERLAY NOTE: this file replaces Stage 5's PaymentService.php
 * wholesale. The only change from Stage 5 is firing FeePaymentRecorded at
 * the end of recordPayment() — the hook point Stage 6's
 * SendPaymentConfirmationNotification listener attaches to. Every other
 * method is unchanged from Stage 5.
 *
 * Section 34 — "Support: Cash, Bank transfer, UPI, Payment gateway,
 * Cheque." Every payment, regardless of method, flows through this one
 * method. Section 34 hard rule — "Never store card details" — is enforced
 * simply by this service's signature never accepting a card number/CVV
 * parameter at all.
 */
class PaymentService
{
    public function __construct(protected AuditService $audit)
    {
    }

    public function recordPayment(
        FeeInvoice $invoice,
        float $amount,
        string $method,
        User $actor,
        ?string $transactionReference = null,
        ?string $gateway = null,
        ?array $gatewayResponse = null,
    ): FeePayment {
        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        if ($invoice->status === 'cancelled') {
            throw new RuntimeException('Cannot record a payment against a cancelled invoice.');
        }

        $maxAcceptable = (float) $invoice->balance_amount;
        if ($amount > $maxAcceptable + 0.01) {
            throw new RuntimeException("This payment ({$amount}) exceeds the outstanding balance ({$maxAcceptable}).");
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $actor, $transactionReference, $gateway, $gatewayResponse) {
            $payment = FeePayment::create([
                'fee_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'transaction_reference' => $transactionReference,
                'amount' => $amount,
                'method' => $method,
                'status' => 'success',
                'gateway' => $gateway,
                'gateway_response' => $gatewayResponse,
                'recorded_by' => $actor->id,
                'paid_at' => now(),
            ]);

            $this->applyPaymentToInvoice($invoice, $amount);

            $receipt = PaymentReceipt::create([
                'fee_payment_id' => $payment->id,
                'receipt_number' => $this->generateReceiptNumber($invoice),
                'pdf_status' => 'queued',
            ]);
            GeneratePaymentReceiptPdf::dispatch($receipt->id);

            $this->audit->logSchool($actor->id, 'fee_payment.recorded', FeePayment::class, $payment->id, null, [
                'invoice_id' => $invoice->id, 'amount' => $amount, 'method' => $method,
            ]);

            event(new FeePaymentRecorded($payment));

            return $payment->fresh('receipt');
        });
    }

    protected function applyPaymentToInvoice(FeeInvoice $invoice, float $amount): void
    {
        $invoice->paid_amount = (float) $invoice->paid_amount + $amount;
        $invoice->balance_amount = max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        $invoice->status = $invoice->balance_amount <= 0.01 ? 'paid' : 'partially_paid';
        $invoice->save();
    }

    public function reverseRefundedAmount(FeeInvoice $invoice, float $refundedAmount): void
    {
        $invoice->paid_amount = max(0, (float) $invoice->paid_amount - $refundedAmount);
        $invoice->balance_amount = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        $invoice->status = $invoice->paid_amount <= 0 ? 'refunded' : 'partially_paid';
        $invoice->save();
    }

    protected function generateReceiptNumber(FeeInvoice $invoice): string
    {
        $year = now()->format('y');
        $sequence = PaymentReceipt::where('school_id', $invoice->school_id)->count() + 1;

        return "RCPT{$year}-" . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
