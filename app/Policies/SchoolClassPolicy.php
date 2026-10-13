<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\SchoolClass;
use App\Models\User;
class SchoolClassPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('classes_sections.view'); }
    public function create(User $user): bool { return $this->entitlements->can('classes_sections.create'); }
    public function update(User $user, SchoolClass $class): bool { return $this->entitlements->can('classes_sections.edit'); }
    public function delete(User $user, SchoolClass $class): bool { return $this->entitlements->can('classes_sections.delete'); }
}
