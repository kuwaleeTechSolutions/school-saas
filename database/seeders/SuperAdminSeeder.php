<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class SuperAdminSeeder extends Seeder {
    public function run(): void {
        Admin::updateOrCreate(['email' => 'superadmin@schoolsaas.test'], ['name' => 'Platform Super Admin', 'password' => Hash::make('ChangeMe!123'), 'status' => 'active']);
        $this->command?->warn('Seeded Super Admin: superadmin@schoolsaas.test / ChangeMe!123');
    }
}
