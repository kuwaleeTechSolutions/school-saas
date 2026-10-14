<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/super-admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
            'entitlement' => \App\Http\Middleware\EntitlementGate::class,
        ]);
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\IdentifyTenant::class
        );

        $middleware->validateCsrfTokens(except: [
            'webhooks/gateway/*',
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('super-admin')) {
                return route('super-admin.login');
          }
        
            if ($request->is('super-admin/*')) {
                return route('super-admin.login');
            }
        
            $schoolSlug = $request->route('school_slug');
        
            if (! $schoolSlug) {
                $tenant = app(\App\Domain\Shared\Tenancy\TenantContext::class);
        
                if ($tenant->hasSchool()) {
                    $schoolSlug = $tenant->school()->slug;
                }
            }
        
            if (! $schoolSlug) {
                return url('/');
            }
        
            if ($request->route() && ! $request->route()->getDomain()) {
                return url('/school/' . $schoolSlug . '/login');
            }
        
            return route('tenant.login', [
                'school_slug' => $schoolSlug,
            ]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
