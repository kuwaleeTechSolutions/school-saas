<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Models\User;
class UserPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('staff.view') || $user->hasRole('School Owner'); }
    public function create(User $user): bool { return $this->entitlements->can('staff.create') || $user->hasRole('School Owner'); }
    public function update(User $user, User $target): bool
    {
        if ($target->hasRole('School Owner') && ! $user->hasRole('School Owner')) return false;
        return $this->entitlements->can('staff.edit') || $user->hasRole('School Owner');
    }
    public function delete(User $user, User $target): bool
    {
        if ($target->is($user)) return false;
        return $user->hasRole('School Owner');
    }
}
