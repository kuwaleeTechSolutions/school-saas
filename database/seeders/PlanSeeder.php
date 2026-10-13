<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\Feature;
use App\Domain\Platform\Models\ModuleRegistry;
use App\Domain\Platform\Models\Permission;
use App\Domain\Platform\Models\Plan;
use Illuminate\Database\Seeder;
class PlanSeeder extends Seeder {
    public function run(): void { $this->seedStarter(); $this->seedProfessional(); $this->seedEnterprise(); }

    protected function seedStarter(): void {
        $plan = Plan::updateOrCreate(['code' => 'starter'], ['name' => 'Starter', 'description' => 'For small schools getting started.', 'display_order' => 1, 'is_recommended' => false, 'status' => 'active', 'trial_days' => 14]);
        $plan->prices()->updateOrCreate(['billing_cycle' => 'monthly'], ['price' => 999, 'currency' => 'INR']);
        $this->attachModules($plan, ['dashboard','students','attendance','fees','communication']);
        $this->attachFeatures($plan, ['dashboard.core','students.core','attendance.core','communication.core','fees.view_fees','fees.fee_structure','fees.payment_recording']);
        $this->attachPermissions($plan, [
            'dashboard.view','dashboard.create','dashboard.edit','dashboard.export',
            'students.view','students.create','students.edit','students.export',
            'attendance.view','attendance.create','attendance.edit','attendance.export','attendance.correct',
            'communication.view','communication.create','communication.edit','communication.export',
            'fees.view','fees.create','fees.edit','fees.payment.view','fees.payment.create',
        ]);
        $this->attachLimits($plan, ['students' => 500, 'teachers' => 30, 'staff' => 50, 'storage_mb' => 2048]);
    }

    protected function seedProfessional(): void {
        $plan = Plan::updateOrCreate(['code' => 'professional'], ['name' => 'Professional', 'description' => 'For growing schools needing full workflows.', 'display_order' => 2, 'is_recommended' => true, 'status' => 'active', 'trial_days' => 14]);
        $plan->prices()->updateOrCreate(['billing_cycle' => 'monthly'], ['price' => 2499, 'currency' => 'INR']);
        $modules = ['dashboard','students','parents','teachers','admission','academics','classes_sections','subjects','attendance','examinations','results','fees','homework','assignments','timetable','parent_portal','teacher_portal','communication','reports'];
        $this->attachModules($plan, $modules);
        $features = collect($modules)->reject(fn($m) => $m === 'fees')->map(fn($m) => "{$m}.core")
            ->push('fees.view_fees','fees.fee_structure','fees.invoice_generation','fees.payment_recording','fees.discounts_scholarships','fees.outstanding_report','fees.financial_dashboard','fees.online_payment','examinations.marks_entry')->all();
        $this->attachFeatures($plan, $features);
        $corePermissions = collect($modules)->reject(fn($m) => $m === 'fees')->flatMap(fn($m) => ["{$m}.view","{$m}.create","{$m}.edit","{$m}.delete","{$m}.export"])->all();
        $this->attachPermissions($plan, array_merge($corePermissions, [
            'fees.view','fees.create','fees.edit','fees.delete','fees.export','fees.payment.view','fees.payment.create','fees.payment.export',
            'admission.approve','admission.reject','attendance.correct',
            'examinations.marks.enter','examinations.marks.approve','results.publish',
        ]));
        $this->attachLimits($plan, ['students' => 2000, 'teachers' => 150, 'staff' => 300, 'storage_mb' => 20480]);
    }

    protected function seedEnterprise(): void {
        $plan = Plan::updateOrCreate(['code' => 'enterprise'], ['name' => 'Enterprise', 'description' => 'Every module, unlimited scale.', 'display_order' => 3, 'is_recommended' => false, 'status' => 'active', 'trial_days' => 14]);
        $plan->prices()->updateOrCreate(['billing_cycle' => 'monthly'], ['price' => 7999, 'currency' => 'INR']);
        $plan->modules()->sync(ModuleRegistry::pluck('id'));
        $plan->features()->sync(Feature::pluck('id'));
        $plan->permissions()->sync(Permission::pluck('id'));
        $this->attachLimits($plan, ['students' => null, 'teachers' => null, 'staff' => null, 'storage_mb' => 102400]);
    }

    protected function attachModules(Plan $plan, array $codes): void { $plan->modules()->sync(ModuleRegistry::whereIn('code', $codes)->pluck('id')); }
    protected function attachFeatures(Plan $plan, array $codes): void { $plan->features()->sync(Feature::whereIn('code', $codes)->pluck('id')); }
    protected function attachPermissions(Plan $plan, array $names): void { $plan->permissions()->sync(Permission::whereIn('name', $names)->pluck('id')); }
    protected function attachLimits(Plan $plan, array $limits): void {
        foreach ($limits as $metric => $maxValue) { $plan->limits()->updateOrCreate(['metric' => $metric], ['max_value' => $maxValue]); }
    }
}
