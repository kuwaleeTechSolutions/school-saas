<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\Feature;
use App\Domain\Platform\Models\ModuleRegistry;
use App\Domain\Platform\Models\Permission;
use Illuminate\Database\Seeder;
class PermissionRegistrySeeder extends Seeder {
    public function run(): void {
        $this->seedFeesModule();
        $this->seedRemainingModules();
        $this->seedStage3ExtraActions();
        $this->seedStage4ExtraActions();
    }
    protected function seedFeesModule(): void {
        $module = ModuleRegistry::where('code', 'fees')->firstOrFail();
        foreach (['view','create','edit','delete','approve','reject','refund','export'] as $action) {
            $this->makePermission($module, null, "fees.{$action}", $action);
        }
        $paymentFeature = Feature::where('code', 'fees.payment_recording')->first();
        if ($paymentFeature) {
            foreach (['view','create','export'] as $action) {
                $this->makePermission($module, $paymentFeature, "fees.payment.{$action}", $action);
            }
        }
    }
    protected function seedRemainingModules(): void {
        $modules = ModuleRegistry::where('code', '!=', 'fees')->get();
        foreach ($modules as $module) {
            $coreFeature = Feature::where('code', "{$module->code}.core")->first();
            foreach (['view','create','edit','delete','export'] as $action) {
                $this->makePermission($module, $coreFeature, "{$module->code}.{$action}", $action);
            }
        }
    }
    protected function seedStage3ExtraActions(): void {
        $admission = ModuleRegistry::where('code', 'admission')->first();
        if ($admission) {
            foreach (['approve','reject'] as $action) { $this->makePermission($admission, null, "admission.{$action}", $action); }
        }
        $attendance = ModuleRegistry::where('code', 'attendance')->first();
        if ($attendance) { $this->makePermission($attendance, null, 'attendance.correct', 'approve'); }
    }
    protected function seedStage4ExtraActions(): void {
        $examinations = ModuleRegistry::where('code', 'examinations')->first();
        if ($examinations) {
            $marksFeature = Feature::updateOrCreate(['code' => 'examinations.marks_entry'], ['module_id' => $examinations->id, 'name' => 'Marks Entry', 'sort_order' => 100, 'is_active' => true]);
            $this->makePermission($examinations, $marksFeature, 'examinations.marks.enter', 'create');
            $this->makePermission($examinations, $marksFeature, 'examinations.marks.approve', 'approve');
        }
        $results = ModuleRegistry::where('code', 'results')->first();
        if ($results) {
            $this->makePermission($results, null, 'results.publish', 'publish');
            $this->makePermission($results, null, 'results.edit_published', 'edit');
        }
    }
    protected function makePermission(ModuleRegistry $module, ?Feature $feature, string $name, string $action): void {
        Permission::updateOrCreate(['name' => $name, 'guard_name' => 'web'], ['module_id' => $module->id, 'feature_id' => $feature?->id, 'action' => $action]);
    }
}
