<?php
namespace App\Domain\Shared\Permission;
use App\Domain\Shared\Permission\Contracts\PermissionServiceContract;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class PermissionService implements PermissionServiceContract
{
    protected const ROLE_SCOPES = [
        'Principal' => 'all_school_students', 'Vice Principal' => 'all_school_students',
        'Administrator' => 'all_school_students', 'Teacher' => 'assigned_classes_only',
        'Class Teacher' => 'assigned_classes_only', 'Accountant' => 'financial_data_only',
        'Parent' => 'own_children_only', 'Student' => 'self_only',
    ];
    public function userHasPermission(?User $user, string $permissionCode): bool
    {
        if (! $user) return false;
        return $user->can($permissionCode);
    }
    public function roleHasPermission(Role $role, string $permissionCode): bool { return $role->hasPermissionTo($permissionCode); }
    public function effectiveScope(User $user, string $permissionCode): ?string
    {
        return self::ROLE_SCOPES[$user->roles()->value('name')] ?? null;
    }
    public function assignableRolesFor(School $school): Collection
    {
        return Role::whereNull('school_id')->orWhere('school_id', $school->id)->orderBy('name')->get();
    }
    public function assignablePermissionsFor(School $school): Collection
    {
        $subscription = $school->activeSubscription()->first();
        return $subscription ? $subscription->plan->permissions()->orderBy('name')->get() : collect();
    }
}
