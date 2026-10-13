<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\VisitorLog;
use App\Models\User;
class VisitorLogPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('visitor_management.view'); }
    public function create(User $user): bool { return $this->entitlements->can('visitor_management.create'); }
    public function update(User $user, VisitorLog $log): bool { return $this->entitlements->can('visitor_management.edit'); }
}
