<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\FeeRefund;
use App\Models\User;
class FeeRefundPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('fees.view'); }
    public function request(User $user): bool { return $this->entitlements->can('fees.refund'); }
    public function approve(User $user, FeeRefund $refund): bool { return $this->entitlements->can('fees.approve'); }
    public function reject(User $user, FeeRefund $refund): bool { return $this->entitlements->can('fees.reject'); }
}
