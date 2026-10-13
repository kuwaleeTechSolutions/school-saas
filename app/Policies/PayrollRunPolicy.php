<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\PayrollRun;
use App\Models\User;
class PayrollRunPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('payroll.view'); }
    public function create(User $user): bool { return $this->entitlements->can('payroll.create'); }
    public function finalize(User $user, PayrollRun $run): bool { return $this->entitlements->can('payroll.approve'); }
}
