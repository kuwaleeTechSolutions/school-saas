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

        $middleware->validateCsrfTokens(except: [
            'webhooks/gateway/*',
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('super-admin*')) {
                return route('super-admin.login');
            }

            if (
                $request->route('school_slug') &&
                ! $request->route()->getDomain()
            ) {
                return url(
                    '/school/' .
                    $request->route('school_slug') .
                    '/login'
                );
            }

            return route('tenant.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
