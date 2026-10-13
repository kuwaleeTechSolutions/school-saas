<?php
namespace App\Domain\Shared\Billing;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Shared\Billing\Contracts\PaymentGatewayContract;
use App\Domain\Shared\Billing\Gateways\RazorpayGateway;
use App\Domain\Shared\Billing\Gateways\StripeGateway;
use InvalidArgumentException;
class PaymentGatewayFactory
{
    public function make(string $provider): PaymentGatewayContract
    {
        $credential = PaymentGatewayCredential::where('provider', $provider)->first();
        return match ($provider) {
            'razorpay' => new RazorpayGateway($credential),
            'stripe' => new StripeGateway($credential),
            default => throw new InvalidArgumentException("Unknown payment gateway provider '{$provider}'."),
        };
    }
}
