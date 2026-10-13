<?php
namespace App\Domain\Shared\Billing\Contracts;
use App\Domain\Platform\Models\PlatformInvoice;
/** Every gateway implements this. Only createCheckoutSession() needs a live SDK/API; the rest is local computation. */
interface PaymentGatewayContract
{
    public function providerName(): string;
    public function createCheckoutSession(PlatformInvoice $invoice): string;
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool;
    /** @return array{event_type:?string, external_event_id:?string, invoice_reference:?string, status:string, gateway_reference:?string} */
    public function parseWebhookPayload(array $payload): array;
}
