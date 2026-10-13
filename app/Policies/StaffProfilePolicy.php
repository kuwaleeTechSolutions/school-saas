<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\StaffProfile;
use App\Models\User;
class StaffProfilePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('hr.view'); }
    public function view(User $user, StaffProfile $staff): bool { return $this->entitlements->can('hr.view') || $staff->user_id === $user->id; }
    public function create(User $user): bool { return $this->entitlements->can('hr.create'); }
    public function update(User $user, StaffProfile $staff): bool { return $this->entitlements->can('hr.edit'); }
}
