<?php
namespace App\Providers;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;
class TenancyServiceProvider extends ServiceProvider { public function register(): void { $this->app->singleton(TenantContext::class); } }
