<?php
namespace App\Domain\Shared\Permission\Contracts;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
interface PermissionServiceContract
{
    public function userHasPermission(?User $user, string $permissionCode): bool;
    public function roleHasPermission(Role $role, string $permissionCode): bool;
    public function effectiveScope(User $user, string $permissionCode): ?string;
    public function assignableRolesFor(\App\Domain\Tenant\Models\School $school): Collection;
    public function assignablePermissionsFor(\App\Domain\Tenant\Models\School $school): Collection;
}
