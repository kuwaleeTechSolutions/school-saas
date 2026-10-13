<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * STAGE 6 OVERLAY NOTE: this file replaces the RoleSeeder from Stages 1-4
 * wholesale. The only change is adding 'teacher_portal' to the module list
 * for Teacher and Class Teacher roles, so teacher_portal.view is actually
 * granted (it previously existed in the permission registry since Stage 1
 * but was never assigned to any teaching role). Every other role's
 * permission set is unchanged.
 *
 * Section G.1 — system (template) roles, school_id NULL, available to every
 * school. Permission assignment reflects each role's "Typical Scope" from
 * Section G.1. Remember: a role having a permission is necessary but NOT
 * sufficient — EntitlementService::can() additionally requires the
 * school's active package to allow it (Section F.4).
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permission::all();
        $feesPermissions = Permission::where('name', 'like', 'fees.%')->get();

        $roles = [
            'School Owner' => $all,
            'Principal' => $all,
            'Vice Principal' => $all,
            'Administrator' => $all->reject(fn ($p) => str_starts_with($p->name, 'fees.')),
            'Accountant' => $feesPermissions,
            'Teacher' => $this->permissionsFor(['attendance', 'homework', 'assignments', 'examinations', 'teacher_portal'])
                ->reject(fn ($p) => in_array($p->name, ['examinations.marks.approve', 'results.publish', 'results.edit_published'])),
            'Class Teacher' => $this->permissionsFor(['attendance', 'homework', 'assignments', 'examinations', 'results', 'teacher_portal'])
                ->reject(fn ($p) => in_array($p->name, ['results.publish', 'results.edit_published'])),
            'Librarian' => $this->permissionsFor(['library']),
            'Transport Manager' => $this->permissionsFor(['transport']),
            'HR Manager' => $this->permissionsFor(['hr', 'payroll', 'leave']),
            'Receptionist' => $this->permissionsFor(['visitor_management', 'complaints']),
            'Security Guard' => Permission::where('name', 'visitor_management.view')
                ->orWhere('name', 'visitor_management.create')->get(),
            'Parent' => Permission::where('name', 'parent_portal.view')->get(),
            'Student' => Permission::where('name', 'student_portal.view')->get(),
        ];

        foreach ($roles as $name => $permissions) {
            $role = Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web', 'school_id' => null],
            );

            $role->syncPermissions($permissions);
        }
    }

    protected function permissionsFor(array $moduleCodes): \Illuminate\Support\Collection
    {
        return Permission::where(function ($query) use ($moduleCodes) {
            foreach ($moduleCodes as $code) {
                $query->orWhere('name', 'like', "{$code}.%");
            }
        })->get();
    }
}
