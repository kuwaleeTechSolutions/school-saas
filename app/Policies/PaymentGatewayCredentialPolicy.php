<?php
namespace App\Policies;
use App\Domain\Platform\Models\Admin;
use App\Domain\Platform\Models\PaymentGatewayCredential;
/** Super Admin only (admin guard) — mirrors PlanPolicy from Stage 1. */
class PaymentGatewayCredentialPolicy
{
    public function viewAny(Admin $admin): bool { return $admin->status === 'active'; }
    public function create(Admin $admin): bool { return $admin->status === 'active'; }
    public function update(Admin $admin, PaymentGatewayCredential $m): bool { return $admin->status === 'active'; }
}
