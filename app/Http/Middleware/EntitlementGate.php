<?php
namespace App\Http\Middleware;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EntitlementGate
{
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        if (! $this->entitlements->moduleEnabled($moduleCode)) {
            return response()->view('components.feature-lock', ['moduleCode' => $moduleCode], 403);
        }
        return $next($request);
    }
}
