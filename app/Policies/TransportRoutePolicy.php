<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\TransportRoute;
use App\Models\User;
class TransportRoutePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('transport.view'); }
    public function create(User $user): bool { return $this->entitlements->can('transport.create'); }
    public function update(User $user, TransportRoute $route): bool { return $this->entitlements->can('transport.edit'); }
    public function delete(User $user, TransportRoute $route): bool { return $this->entitlements->can('transport.delete'); }
}
