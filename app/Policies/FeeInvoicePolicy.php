<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Models\User;
class FeeInvoicePolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('fees.view'); }
    public function view(User $user, FeeInvoice $invoice): bool { return $this->entitlements->can('fees.view'); }
    public function generate(User $user): bool { return $this->entitlements->can('fees.create'); }
    public function export(User $user): bool { return $this->entitlements->can('fees.export'); }
}
