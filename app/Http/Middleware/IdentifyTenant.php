<?php

namespace App\Http\Middleware;

use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(protected TenantContext $tenant)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->resolveSlug($request);

        if (! $slug) {
            abort(404, 'School not identified.');
        }

        $school = School::where('slug', $slug)->first();

        if (! $school) {
            abort(404, 'School not found.');
        }

        if ($school->status === 'cancelled' && $school->deleted_at !== null) {
            abort(404, 'School not found.');
        }

        $this->tenant->set($school);

        URL::defaults([
            'school_slug' => $slug,
        ]);

        config([
            'app.timezone' => $school->timezone ?? config('app.timezone'),
        ]);

        if (Auth::check() && Auth::user()->school_id !== $school->id) {
            Auth::logout();
        
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        
            return redirect()->route('tenant.login');
        }

        return $next($request);
    }

    protected function resolveSlug(Request $request): ?string
    {
        $host = $request->getHost();
        $suffix = config('platform.tenant_domain_suffix', '.localhost');

        if (str_ends_with($host, $suffix)) {
            $subdomain = substr($host, 0, -strlen($suffix));

            if ($subdomain !== '' && $subdomain !== 'www') {
                return $subdomain;
            }
        }

        if ($request->route('school_slug')) {
            return $request->route('school_slug');
        }

        return null;
    }
}