<?php
namespace App\Providers;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Entitlement\EntitlementService;
use App\Domain\Shared\Permission\Contracts\PermissionServiceContract;
use App\Domain\Shared\Permission\PermissionService;
use Illuminate\Support\ServiceProvider;
class EntitlementServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(PermissionServiceContract::class, PermissionService::class);
        $this->app->bind(EntitlementServiceContract::class, EntitlementService::class);
    }
}
