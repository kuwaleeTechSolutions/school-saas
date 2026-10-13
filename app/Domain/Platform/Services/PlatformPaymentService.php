<?php
namespace App\Domain\Platform\Services;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Models\PlatformPayment;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Billing\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use RuntimeException;
/** Manual methods apply immediately; gateway payments stay 'pending' until a verified webhook calls confirmGatewayPayment(). */
class PlatformPaymentService
{
    public function __construct(protected AuditService $audit, protected PaymentGatewayFactory $gateways) {}
    public function recordManualPayment(PlatformInvoice $invoice, float $amount, string $method, int $adminId, ?string $reference = null): PlatformPayment
    {
        if (! in_array($method, ['bank_transfer','cheque'], true)) throw new RuntimeException('recordManualPayment only accepts bank_transfer or cheque.');
        $this->guardAmount($invoice, $amount);
        return DB::transaction(function () use ($invoice, $amount, $method, $adminId, $reference) {
            $payment = PlatformPayment::create(['platform_invoice_id'=>$invoice->id,'school_id'=>$invoice->school_id,'method'=>$method,'gateway_reference'=>$reference,'amount'=>$amount,'status'=>'success','recorded_by'=>$adminId,'paid_at'=>now()]);
            $this->applyToInvoice($invoice, $amount);
            $this->audit->logPlatform($adminId, 'platform_payment.recorded', PlatformPayment::class, $payment->id, null, ['invoice_id'=>$invoice->id,'amount'=>$amount,'method'=>$method]);
            return $payment->fresh();
        });
    }
    /** The pending row is created first so it survives even if checkout creation throws (e.g. SDK not installed). */
    public function initiateGatewayPayment(PlatformInvoice $invoice, string $provider): array
    {
        $this->guardAmount($invoice, (float) $invoice->balance_amount);
        $gateway = $this->gateways->make($provider);
        $payment = PlatformPayment::create(['platform_invoice_id'=>$invoice->id,'school_id'=>$invoice->school_id,'method'=>'gateway','gateway'=>$provider,'amount'=>$invoice->balance_amount,'status'=>'pending']);
        return ['payment' => $payment, 'checkout_url' => $gateway->createCheckoutSession($invoice)];
    }
    public function confirmGatewayPayment(PlatformPayment $payment, array $gatewayResponse): PlatformPayment
    {
        return DB::transaction(function () use ($payment, $gatewayResponse) {
            $locked = PlatformPayment::whereKey($payment->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') return $locked; // idempotent: never apply twice
            $locked->update(['status'=>'success','gateway_response'=>$gatewayResponse,'paid_at'=>now()]);
            $this->applyToInvoice($locked->invoice, (float) $locked->amount);
            $this->audit->logPlatform(null, 'platform_payment.gateway_confirmed', PlatformPayment::class, $locked->id);
            return $locked->fresh();
        });
    }
    public function markGatewayPaymentFailed(PlatformPayment $payment, array $gatewayResponse): PlatformPayment
    {
        if ($payment->status !== 'pending') return $payment;
        $payment->update(['status'=>'failed','gateway_response'=>$gatewayResponse]);
        $this->audit->logPlatform(null, 'platform_payment.gateway_failed', PlatformPayment::class, $payment->id);
        return $payment->fresh();
    }
    protected function guardAmount(PlatformInvoice $invoice, float $amount): void
    {
        if ($amount <= 0) throw new RuntimeException('Payment amount must be greater than zero.');
        if ($amount > (float) $invoice->balance_amount + 0.01) throw new RuntimeException("This payment ({$amount}) exceeds the outstanding balance ({$invoice->balance_amount}).");
    }
    protected function applyToInvoice(PlatformInvoice $invoice, float $amount): void
    {
        $invoice->paid_amount = (float) $invoice->paid_amount + $amount;
        $invoice->balance_amount = max(0, (float) $invoice->total_amount - (float) $invoice->paid_amount);
        $invoice->status = $invoice->balance_amount <= 0.01 ? 'paid' : 'partially_paid';
        $invoice->save();
    }
}
