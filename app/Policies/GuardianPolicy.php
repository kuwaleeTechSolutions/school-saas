<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Guardian;
use App\Models\User;
class GuardianPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('parents.view'); }
    public function view(User $user, Guardian $guardian): bool { return $this->entitlements->can('parents.view'); }
    public function create(User $user): bool { return $this->entitlements->can('parents.create'); }
    public function update(User $user, Guardian $guardian): bool { return $this->entitlements->can('parents.edit'); }
    public function delete(User $user, Guardian $guardian): bool { return $this->entitlements->can('parents.delete'); }
}
