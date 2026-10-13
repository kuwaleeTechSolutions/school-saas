<?php
namespace App\Policies;
use App\Domain\Platform\Models\Admin;
use App\Domain\Platform\Models\PlatformInvoice;
/** Super Admin only (admin guard) — mirrors PlanPolicy from Stage 1. */
class PlatformInvoicePolicy
{
    public function viewAny(Admin $admin): bool { return $admin->status === 'active'; }
    public function create(Admin $admin): bool { return $admin->status === 'active'; }
    public function update(Admin $admin, PlatformInvoice $m): bool { return $admin->status === 'active'; }
    public function recordPayment(Admin $admin, PlatformInvoice $m): bool { return $admin->status === 'active'; }
}
