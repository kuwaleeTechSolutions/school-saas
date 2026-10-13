<?php
namespace App\Domain\Shared\Entitlement;
use App\Domain\Platform\Models\Feature;
use App\Domain\Platform\Models\ModuleRegistry;
use App\Domain\Platform\Models\Permission;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Permission\Contracts\PermissionServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;

class EntitlementService implements EntitlementServiceContract
{
    protected array $moduleCache = [];
    protected array $featureCache = [];
    protected array $permissionCache = [];
    public function __construct(protected TenantContext $tenant, protected PermissionServiceContract $permissions) {}
    protected function activePlanId(): ?int
    {
        if (! $this->tenant->hasSchool()) return null;
        return $this->tenant->school()->activeSubscription()->first()?->plan_id;
    }
    public function moduleEnabled(string $moduleCode): bool
    {
        $planId = $this->activePlanId();
        if (! $planId) return false;
        return $this->moduleCache[$planId][$moduleCode] ??= (function () use ($planId, $moduleCode) {
            $module = ModuleRegistry::where('code', $moduleCode)->where('is_active', true)->first();
            if (! $module) return false;
            return \App\Domain\Platform\Models\Plan::find($planId)?->modules()->where('modules.id', $module->id)->exists() ?? false;
        })();
    }
    public function featureEnabled(string $featureCode): bool
    {
        $planId = $this->activePlanId();
        if (! $planId) return false;
        return $this->featureCache[$planId][$featureCode] ??= (function () use ($planId, $featureCode) {
            $feature = Feature::where('code', $featureCode)->where('is_active', true)->first();
            if (! $feature) return false;
            if (! $this->moduleEnabled($feature->module->code)) return false;
            return \App\Domain\Platform\Models\Plan::find($planId)?->features()->where('features.id', $feature->id)->exists() ?? false;
        })();
    }
    public function permissionAllowedByPlan(string $permissionCode): bool
    {
        $planId = $this->activePlanId();
        if (! $planId) return false;
        return $this->permissionCache[$planId][$permissionCode] ??= (function () use ($planId, $permissionCode) {
            $permission = Permission::where('name', $permissionCode)->first();
            if (! $permission) return false;
            if ($permission->feature && ! $this->featureEnabled($permission->feature->code)) return false;
            return \App\Domain\Platform\Models\Plan::find($planId)?->permissions()->where('permissions.id', $permission->id)->exists() ?? false;
        })();
    }
    public function limitAvailable(string $metric, int $requested = 1): bool
    {
        $remaining = $this->remaining($metric);
        if ($remaining === null) return true;
        return $remaining >= $requested;
    }
    public function remaining(string $metric): ?int
    {
        $planId = $this->activePlanId();
        if (! $planId || ! $this->tenant->hasSchool()) return 0;
        $limit = \App\Domain\Platform\Models\PlanLimit::where('plan_id', $planId)->where('metric', $metric)->first();
        if (! $limit || $limit->max_value === null) return null;
        $used = \App\Domain\Tenant\Models\SchoolUsageCounter::where('school_id', $this->tenant->currentSchoolId())->where('metric', $metric)->value('current_value') ?? 0;
        return max(0, $limit->max_value - $used);
    }
    public function can(string $permissionCode): bool
    {
        return $this->permissionAllowedByPlan($permissionCode) && $this->permissions->userHasPermission(auth()->user(), $permissionCode);
    }
}
