<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Subject;
use App\Models\User;
class SubjectPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('subjects.view'); }
    public function create(User $user): bool { return $this->entitlements->can('subjects.create'); }
    public function update(User $user, Subject $subject): bool { return $this->entitlements->can('subjects.edit'); }
    public function delete(User $user, Subject $subject): bool { return $this->entitlements->can('subjects.delete'); }
}
