<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
/** Usage: ApiRequireAbility::class.':parent'. A token lacking the ability gets 403 — a Parent token can never reach teacher endpoints. */
class ApiRequireAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        if (! $request->user()?->tokenCan($ability)) return response()->json(['message' => 'This token is not permitted to use this endpoint.'], 403);
        return $next($request);
    }
}
