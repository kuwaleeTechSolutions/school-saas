<?php
namespace App\Http\Middleware;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
/**
 * API authentication + tenant binding in ONE middleware, so ordering can never go wrong.
 * Why it exists: User is tenant-scoped and the scope fails closed with no tenant, so Sanctum could not even load
 * the token owner. The tenant is therefore derived from the TOKEN'S OWNER (never from a header or body field),
 * then Sanctum authenticates normally. A school without an operational subscription is locked out of the API too.
 */
class ApiAuthenticate
{
    public function __construct(protected TenantContext $tenant) {}
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        $token = $plain ? PersonalAccessToken::findToken($plain) : null;
        if (! $token || $token->tokenable_type !== User::class) return $this->deny('Unauthenticated.', 401);
        $owner = User::withoutGlobalScopes()->find($token->tokenable_id);
        $school = $owner?->school_id ? School::find($owner->school_id) : null;
        if (! $school) return $this->deny('Unauthenticated.', 401);
        if (! $school->activeSubscription()->first()?->allowsOperationalAccess()) return $this->deny('This school\'s subscription is not active.', 403);
        $this->tenant->set($school);
        Auth::shouldUse('sanctum');
        if (! Auth::guard('sanctum')->check()) return $this->deny('Unauthenticated.', 401);
        return $next($request);
    }
    protected function deny(string $message, int $status): Response { return response()->json(['message' => $message], $status); }
}
