<?php
namespace App\Domain\Shared\Billing\Gateways;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Domain\Shared\Billing\Contracts\PaymentGatewayContract;
use RuntimeException;
/** Same honesty split as RazorpayGateway: signature verification is real; checkout creation needs stripe/stripe-php. */
class StripeGateway implements PaymentGatewayContract
{
    public function __construct(protected ?PaymentGatewayCredential $credential = null) {}
    public function providerName(): string { return 'stripe'; }
    public function createCheckoutSession(PlatformInvoice $invoice): string
    {
        if (! $this->credential?->isConfigured()) throw new RuntimeException('Stripe is not configured. Add API credentials via Gateway Settings.');
        throw new RuntimeException('Stripe checkout creation requires the stripe/stripe-php SDK (not installed). Run `composer require stripe/stripe-php` and implement the Checkout Sessions call here.');
    }
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        if (! $this->credential?->webhook_secret) return false;
        $parts = [];
        foreach (explode(',', $signatureHeader) as $piece) { [$k, $v] = array_pad(explode('=', $piece, 2), 2, null); if ($k) $parts[trim($k)] = $v; }
        if (empty($parts['t']) || empty($parts['v1'])) return false;
        return hash_equals(hash_hmac('sha256', $parts['t'] . '.' . $rawPayload, $this->credential->webhook_secret), $parts['v1']);
    }
    public function parseWebhookPayload(array $payload): array
    {
        $type = $payload['type'] ?? null;
        $obj = $payload['data']['object'] ?? [];
        return [
            'event_type' => $type, 'external_event_id' => $payload['id'] ?? null,
            'invoice_reference' => $obj['metadata']['platform_invoice_ulid'] ?? null,
            'status' => $type === 'checkout.session.completed' ? 'success' : 'failed',
            'gateway_reference' => $obj['id'] ?? null,
        ];
    }
}
