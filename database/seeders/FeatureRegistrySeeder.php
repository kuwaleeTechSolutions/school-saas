<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\Feature;
use App\Domain\Platform\Models\ModuleRegistry;
use Illuminate\Database\Seeder;
class FeatureRegistrySeeder extends Seeder {
    public function run(): void {
        $this->seedFeatures('fees', [
            'view_fees'=>'View Fees','fee_structure'=>'Fee Structure','invoice_generation'=>'Invoice Generation',
            'payment_recording'=>'Payment Recording','refunds'=>'Refunds','discounts_scholarships'=>'Discounts & Scholarships',
            'outstanding_report'=>'Outstanding Fees Report','financial_dashboard'=>'Financial Dashboard','online_payment'=>'Online Payment',
        ]);
        $remainingModules = ModuleRegistry::where('code', '!=', 'fees')->pluck('code');
        foreach ($remainingModules as $code) { $this->seedFeatures($code, ['core' => 'Core']); }
    }
    protected function seedFeatures(string $moduleCode, array $features): void {
        $module = ModuleRegistry::where('code', $moduleCode)->firstOrFail();
        $sortOrder = 0;
        foreach ($features as $slug => $name) {
            Feature::updateOrCreate(['code' => "{$moduleCode}.{$slug}"], ['module_id' => $module->id, 'name' => $name, 'sort_order' => $sortOrder++, 'is_active' => true]);
        }
    }
}
