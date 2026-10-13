<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\AdmissionApplication;
use App\Models\User;
class AdmissionApplicationPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('admission.view'); }
    public function view(User $user, AdmissionApplication $application): bool { return $this->entitlements->can('admission.view'); }
    public function create(User $user): bool { return $this->entitlements->can('admission.create'); }
    public function update(User $user, AdmissionApplication $application): bool { return $this->entitlements->can('admission.edit'); }
    public function transition(User $user, AdmissionApplication $application): bool { return $this->entitlements->can('admission.approve'); }
}
