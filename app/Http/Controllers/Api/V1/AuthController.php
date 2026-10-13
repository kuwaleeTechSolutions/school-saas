<?php
namespace App\Http\Controllers\Api\V1;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
class AuthController extends \App\Http\Controllers\Controller
{
    /** Never '*': abilities come from the user's roles only. */
    public const ROLE_ABILITIES = ['Parent' => 'parent', 'Teacher' => 'teacher', 'Class Teacher' => 'teacher', 'Student' => 'student'];

    public function token(Request $request, TenantContext $tenant, AuditService $audit): JsonResponse
    {
        $data = $request->validate(['school' => 'required|string|max:100', 'email' => 'required|email', 'password' => 'required|string', 'device_name' => 'required|string|max:100']);
        $key = 'api-login:'.strtolower($data['school']).'|'.strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) return response()->json(['message' => 'Too many attempts. Try again later.'], 429);
        $fail = fn () => response()->json(['message' => 'These credentials do not match our records.'], 401); // same reply for every failure: no account/school enumeration
        $school = School::where('slug', $data['school'])->first();
        if (! $school || ! $school->activeSubscription()->first()?->allowsOperationalAccess()) { RateLimiter::hit($key, 60); return $fail(); }
        $tenant->set($school);
        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) { RateLimiter::hit($key, 60); return $fail(); }
        RateLimiter::clear($key);
        $abilities = collect(self::ROLE_ABILITIES)->filter(fn ($a, $role) => $user->hasRole($role))->values()->unique()->all();
        if (! $abilities) $abilities = ['staff'];
        $audit->logSchool($user->id, 'api.token_issued', User::class, $user->id, null, ['device' => $data['device_name'], 'abilities' => $abilities]);
        return response()->json(['token' => $user->createToken($data['device_name'], $abilities, now()->addDays(30))->plainTextToken, 'abilities' => $abilities, 'expires_in_days' => 30]);
    }
    public function logout(Request $request): JsonResponse { $request->user()->currentAccessToken()->delete(); return response()->json(['message' => 'Token revoked.']); }
}
