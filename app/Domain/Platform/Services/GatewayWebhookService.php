<?php
namespace App\Domain\Platform\Services;
use App\Domain\Platform\Models\GatewayWebhookEvent;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Platform\Models\PlatformPayment;
use App\Domain\Shared\Billing\PaymentGatewayFactory;
use RuntimeException;
/** Verify signature -> dedupe on (provider, external_event_id) -> log -> apply. All logic lives here, not in the controller. */
class GatewayWebhookService
{
    public function __construct(protected PaymentGatewayFactory $gateways, protected PlatformPaymentService $payments) {}
    public function handle(string $provider, string $rawPayload, string $signatureHeader): GatewayWebhookEvent
    {
        $gateway = $this->gateways->make($provider);
        if (! $gateway->verifyWebhookSignature($rawPayload, $signatureHeader)) throw new RuntimeException('Webhook signature verification failed.');
        $payload = json_decode($rawPayload, true) ?? [];
        $parsed = $gateway->parseWebhookPayload($payload);
        $existing = GatewayWebhookEvent::where('provider', $provider)->where('external_event_id', $parsed['external_event_id'])->first();
        if ($existing) return $existing;
        $event = GatewayWebhookEvent::create(['provider'=>$provider,'external_event_id'=>$parsed['external_event_id'],'event_type'=>$parsed['event_type'],'payload'=>$payload,'status'=>'received']);
        try {
            $this->apply($parsed);
            $event->update(['status'=>'processed','processed_at'=>now()]);
        } catch (\Throwable $e) {
            $event->update(['status'=>'failed','failure_reason'=>substr($e->getMessage(), 0, 500)]);
            throw $e;
        }
        return $event->fresh();
    }
    protected function apply(array $parsed): void
    {
        if (empty($parsed['invoice_reference'])) return;
        $invoice = PlatformInvoice::where('ulid', $parsed['invoice_reference'])->first();
        if (! $invoice) return;
        $payment = PlatformPayment::where('platform_invoice_id', $invoice->id)->where('status', 'pending')->orderByDesc('id')->first();
        if (! $payment) return;
        $parsed['status'] === 'success' ? $this->payments->confirmGatewayPayment($payment, $parsed) : $this->payments->markGatewayPaymentFailed($payment, $parsed);
    }
}
