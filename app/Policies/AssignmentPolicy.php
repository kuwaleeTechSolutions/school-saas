<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Assignment;
use App\Models\User;
class AssignmentPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('assignments.view'); }
    public function create(User $user): bool { return $this->entitlements->can('assignments.create'); }
    public function update(User $user, Assignment $assignment): bool { return $this->entitlements->can('assignments.edit'); }
    public function grade(User $user, Assignment $assignment): bool { return $this->entitlements->can('assignments.edit'); }
}
