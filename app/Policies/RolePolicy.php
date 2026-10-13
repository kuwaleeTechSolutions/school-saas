<?php
namespace App\Policies;
use App\Models\User;
use Spatie\Permission\Models\Role;
class RolePolicy {
    public function viewAny(User $user): bool { return $user->hasAnyRole(['School Owner','Principal','Administrator']); }
    public function create(User $user): bool { return $user->hasAnyRole(['School Owner','Administrator']); }
    public function update(User $user, Role $role): bool
    {
        if ($role->school_id === null) return false;
        return $user->hasAnyRole(['School Owner','Administrator']) && $role->school_id === $user->school_id;
    }
    public function delete(User $user, Role $role): bool { return $this->update($user, $role); }
}
