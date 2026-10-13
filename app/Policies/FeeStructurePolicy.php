<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\FeeStructure;
use App\Models\User;
class FeeStructurePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('fees.view'); }
    public function create(User $user): bool { return $this->entitlements->can('fees.create'); }
    public function update(User $user, FeeStructure $feeStructure): bool { return $this->entitlements->can('fees.edit'); }
    public function delete(User $user, FeeStructure $feeStructure): bool { return $this->entitlements->can('fees.delete'); }
}
