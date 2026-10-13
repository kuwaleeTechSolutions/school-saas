<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\FeePayment;
use App\Models\User;
class FeePaymentPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('fees.payment.view'); }
    public function create(User $user): bool { return $this->entitlements->can('fees.payment.create'); }
    public function export(User $user): bool { return $this->entitlements->can('fees.payment.export'); }
}
