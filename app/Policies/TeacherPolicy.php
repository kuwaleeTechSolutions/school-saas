<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Teacher;
use App\Models\User;
class TeacherPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('teachers.view'); }
    public function view(User $user, Teacher $teacher): bool { return $this->entitlements->can('teachers.view'); }
    public function create(User $user): bool { return $this->entitlements->can('teachers.create'); }
    public function update(User $user, Teacher $teacher): bool { return $this->entitlements->can('teachers.edit'); }
    public function delete(User $user, Teacher $teacher): bool { return $this->entitlements->can('teachers.delete'); }
}
