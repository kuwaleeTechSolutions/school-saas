<?php
namespace App\Providers;
use App\Domain\Platform\Models\Coupon;
use App\Domain\Platform\Models\PaymentGatewayCredential;
use App\Domain\Platform\Models\PlatformInvoice;
use App\Policies\CouponPolicy;
use App\Policies\PaymentGatewayCredentialPolicy;
use App\Policies\PlatformInvoicePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
/**
 * Registers Stage 8's three policies WITHOUT replacing Stage 7's PolicyServiceProvider (unlike earlier overlays,
 * this avoids re-shipping a large file wholesale). Register in bootstrap/providers.php.
 */
class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Coupon::class, CouponPolicy::class);
        Gate::policy(PaymentGatewayCredential::class, PaymentGatewayCredentialPolicy::class);
        Gate::policy(PlatformInvoice::class, PlatformInvoicePolicy::class);
    }
}
