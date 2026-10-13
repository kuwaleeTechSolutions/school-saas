<?php
namespace App\Domain\Shared\Billing\Gateways;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Shared\Billing\Contracts\PaymentGatewayContract;
use RuntimeException;
/**
 * verifyWebhookSignature() is genuinely functional (HMAC-SHA256, local computation).
 * createCheckoutSession() is NOT: it needs the razorpay/razorpay SDK + live credentials, so it throws
 * a clear exception instead of fabricating a checkout URL (Section 144 — no fake features).
 */
class RazorpayGateway implements PaymentGatewayContract
{
    public function __construct(protected ?PaymentGatewayCredential $credential = null) {}
    public function providerName(): string { return 'razorpay'; }
    public function createCheckoutSession(PlatformInvoice $invoice): string
    {
        if (! $this->credential?->isConfigured()) throw new RuntimeException('Razorpay is not configured. Add API credentials via Gateway Settings.');
        throw new RuntimeException('Razorpay checkout creation requires the razorpay/razorpay SDK (not installed). Run `composer require razorpay/razorpay` and implement the Orders API call here.');
    }
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        if (! $this->credential?->webhook_secret) return false;
        return hash_equals(hash_hmac('sha256', $rawPayload, $this->credential->webhook_secret), $signatureHeader);
    }
    public function parseWebhookPayload(array $payload): array
    {
        $event = $payload['event'] ?? null;
        $entity = $payload['payload']['payment']['entity'] ?? [];
        $account = $payload['account_id'] ?? 'unknown-account';
        return [
            'event_type' => $event,
            'external_event_id' => $account . '-' . ($entity['id'] ?? uniqid()),
            'invoice_reference' => $entity['notes']['platform_invoice_ulid'] ?? null,
            'status' => $event === 'payment.captured' ? 'success' : 'failed',
            'gateway_reference' => $entity['id'] ?? null,
        ];
    }
}
