<?php
namespace App\Policies;
use App\Domain\Platform\Models\Admin;
use App\Domain\Platform\Models\Coupon;
/** Super Admin only (admin guard) — mirrors PlanPolicy from Stage 1. */
class CouponPolicy
{
    public function viewAny(Admin $admin): bool { return $admin->status === 'active'; }
    public function create(Admin $admin): bool { return $admin->status === 'active'; }
    public function update(Admin $admin, Coupon $m): bool { return $admin->status === 'active'; }
    public function delete(Admin $admin, Coupon $m): bool { return $admin->status === 'active' && $m->redemptions()->doesntExist(); }
}
