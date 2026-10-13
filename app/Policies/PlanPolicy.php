<?php
namespace App\Policies;
use App\Domain\Platform\Models\Admin;
use App\Domain\Platform\Models\Plan;
class PlanPolicy {
    public function viewAny(Admin $admin): bool { return $admin->status === 'active'; }
    public function create(Admin $admin): bool { return $admin->status === 'active'; }
    public function update(Admin $admin, Plan $plan): bool { return $admin->status === 'active'; }
    public function delete(Admin $admin, Plan $plan): bool { return $admin->status === 'active' && $plan->status !== 'active'; }
}
