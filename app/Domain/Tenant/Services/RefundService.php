<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\FeePayment;
use App\Domain\Tenant\Models\FeeRefund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundService
{
    public function __construct(protected AuditService $audit, protected PaymentService $paymentService) {}

    public function request(FeePayment $payment, float $amount, string $reason, User $actor): FeeRefund
    {
        $alreadyRefunded = $payment->totalRefunded();
        $maxRefundable = (float) $payment->amount - $alreadyRefunded;
        if ($amount <= 0 || $amount > $maxRefundable + 0.01) {
            throw new RuntimeException("Refund amount must be between 0 and {$maxRefundable} (already refunded: {$alreadyRefunded}).");
        }
        $refund = FeeRefund::create([
            'fee_payment_id' => $payment->id, 'amount' => $amount, 'reason' => $reason,
            'status' => 'requested', 'requested_by' => $actor->id,
        ]);
        $this->audit->logSchool($actor->id, 'fee_refund.requested', FeeRefund::class, $refund->id, null, ['payment_id' => $payment->id, 'amount' => $amount]);
        return $refund;
    }

    public function approveAndProcess(FeeRefund $refund, User $actor): FeeRefund
    {
        if ($refund->status !== 'requested') {
            throw new RuntimeException("Only a 'requested' refund can be approved (current status: '{$refund->status}').");
        }
        return DB::transaction(function () use ($refund, $actor) {
            $refund->update(['status' => 'approved', 'approved_by' => $actor->id]);
            $payment = $refund->payment;
            $this->paymentService->reverseRefundedAmount($payment->invoice, (float) $refund->amount);
            $refund->update(['status' => 'processed', 'processed_at' => now()]);
            $this->audit->logSchool($actor->id, 'fee_refund.processed', FeeRefund::class, $refund->id, ['status' => 'requested'], ['status' => 'processed', 'amount' => $refund->amount]);
            return $refund->fresh();
        });
    }

    public function reject(FeeRefund $refund, string $rejectionReason, User $actor): FeeRefund
    {
        if ($refund->status !== 'requested') {
            throw new RuntimeException("Only a 'requested' refund can be rejected (current status: '{$refund->status}').");
        }
        $refund->update(['status' => 'rejected', 'approved_by' => $actor->id, 'rejection_reason' => $rejectionReason]);
        $this->audit->logSchool($actor->id, 'fee_refund.rejected', FeeRefund::class, $refund->id, ['status' => 'requested'], ['status' => 'rejected']);
        return $refund->fresh();
    }
}
