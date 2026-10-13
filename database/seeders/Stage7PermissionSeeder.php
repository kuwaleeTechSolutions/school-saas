<?php

namespace Database\Seeders;

use App\Domain\Platform\Models\ModuleRegistry;
use App\Domain\Platform\Models\Permission;
use App\Domain\Platform\Models\Plan;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class Stage7PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $leaveModule = ModuleRegistry::where('code', 'leave')->firstOrFail();

        Permission::updateOrCreate(
            [
                'name' => 'leave.approve',
                'guard_name' => 'web',
            ],
            [
                'module_id' => $leaveModule->id,
                'feature_id' => null,
                'action' => 'approve',
            ]
        );

        $payrollModule = ModuleRegistry::where('code', 'payroll')->firstOrFail();

        Permission::updateOrCreate(
            [
                'name' => 'payroll.approve',
                'guard_name' => 'web',
            ],
            [
                'module_id' => $payrollModule->id,
                'feature_id' => null,
                'action' => 'approve',
            ]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $enterprise = Plan::where('code', 'enterprise')->firstOrFail();

        $enterprise->modules()->sync(
            ModuleRegistry::pluck('id')
        );

        $enterprise->permissions()->sync(
            Permission::pluck('id')
        );

        $this->call(RoleSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
