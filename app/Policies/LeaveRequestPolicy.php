<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\LeaveRequest;
use App\Models\User;
class LeaveRequestPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('leave.view'); }
    public function create(User $user): bool { return $this->entitlements->can('leave.create'); }
    public function approve(User $user, LeaveRequest $leave): bool { return $this->entitlements->can('leave.approve'); }
}
