<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleRegistrySeeder::class,
            FeatureRegistrySeeder::class,
            PermissionRegistrySeeder::class,
            RoleSeeder::class,
            PlanSeeder::class,
            Stage7PermissionSeeder::class,
            SuperAdminSeeder::class,
            DemoSchoolSeeder::class,
        ]);
    }
}
