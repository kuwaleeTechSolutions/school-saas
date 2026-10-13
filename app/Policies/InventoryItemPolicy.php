<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\InventoryItem;
use App\Models\User;
class InventoryItemPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('inventory.view'); }
    public function create(User $user): bool { return $this->entitlements->can('inventory.create'); }
    public function update(User $user, InventoryItem $item): bool { return $this->entitlements->can('inventory.edit'); }
    public function delete(User $user, InventoryItem $item): bool { return $this->entitlements->can('inventory.delete'); }
}
