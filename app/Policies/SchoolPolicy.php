<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\School;
use App\Models\User;
class SchoolPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function view(User $user, School $school): bool { return $user->school_id === $school->id; }
    public function update(User $user, School $school): bool { return $user->school_id === $school->id && $this->entitlements->can('school.settings.edit'); }
}
