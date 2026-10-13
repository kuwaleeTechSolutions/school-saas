<?php
namespace App\Http\Middleware;
use App\Domain\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function __construct(protected TenantContext $tenant) {}
    public function handle(Request $request, Closure $next): Response
    {
        $subscription = $this->tenant->school()->activeSubscription()->first();
        if (! $subscription || ! $subscription->allowsOperationalAccess()) {
            return redirect()->route('tenant.billing.locked')->with('subscription_status', $subscription?->status ?? 'pending');
        }
        return $next($request);
    }
}
