<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\ReportCard;
use App\Models\User;
class ReportCardPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('results.view'); }
    public function view(User $user, ReportCard $reportCard): bool { return $this->entitlements->can('results.view'); }
    public function generate(User $user): bool { return $this->entitlements->can('results.publish'); }
}
