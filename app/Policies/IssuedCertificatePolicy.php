<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\IssuedCertificate;
use App\Models\User;
class IssuedCertificatePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('certificates.view'); }
    public function create(User $user): bool { return $this->entitlements->can('certificates.create'); }
}
