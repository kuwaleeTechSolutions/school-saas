<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Complaint;
use App\Models\User;
class ComplaintPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Complaint $complaint): bool { return $complaint->raised_by === $user->id || $this->entitlements->can('complaints.view'); }
    public function create(User $user): bool { return true; }
    public function update(User $user, Complaint $complaint): bool { return $this->entitlements->can('complaints.edit'); }
}
