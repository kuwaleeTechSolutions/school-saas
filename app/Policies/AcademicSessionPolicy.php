<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\AcademicSession;
use App\Models\User;
class AcademicSessionPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('academics.view'); }
    public function create(User $user): bool { return $this->entitlements->can('academics.create'); }
    public function update(User $user, AcademicSession $session): bool { return $this->entitlements->can('academics.edit'); }
}
