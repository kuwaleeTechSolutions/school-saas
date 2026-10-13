<?php
namespace Database\Seeders;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolOnboardingProgress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
class DemoSchoolSeeder extends Seeder {
    public function run(): void {
        $school = School::updateOrCreate(['slug' => 'demo-school'], [
            'school_code' => 'DEMO-001', 'name' => 'Demo Public School', 'email' => 'contact@demoschool.test',
            'phone' => '+91-9000000000', 'city' => 'Guwahati', 'district' => 'Kamrup Metropolitan',
            'state' => 'Assam', 'country' => 'India', 'board' => 'SEBA', 'status' => 'active',
            'onboarding_status' => 'in_progress', 'timezone' => 'Asia/Kolkata', 'currency' => 'INR',
        ]);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $subscription = $school->subscriptions()->updateOrCreate(
            ['school_id' => $school->id, 'is_active' => true],
            ['ulid' => (string) Str::ulid(), 'plan_id' => $plan->id, 'billing_cycle' => 'monthly', 'status' => 'active',
             'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'is_active' => true]
        );
        $subscription->events()->create(['event_type' => 'subscription_seeded', 'payload' => ['note' => 'DemoSchoolSeeder'], 'occurred_at' => now()]);
        app(TenantContext::class)->set($school);
        $owner = User::updateOrCreate(['email' => 'owner@demoschool.test'], [
            'school_id' => $school->id, 'name' => 'Demo School Owner', 'password' => Hash::make('ChangeMe!123'),
            'status' => 'active', 'email_verified_at' => now(),
        ]);
        $ownerRole = Role::where('name', 'School Owner')->whereNull('school_id')->first();
        if ($ownerRole && ! $owner->hasRole($ownerRole)) { $owner->assignRole($ownerRole); }
        $session = AcademicSession::updateOrCreate(['school_id' => $school->id, 'name' => '2026-27'], ['starts_on' => '2026-04-01', 'ends_on' => '2027-03-31']);
        $session->markAsCurrent();
        foreach (['school_info','branding','academic_session','classes','sections','subjects','admin_users','package_confirmation','import_students','complete_setup'] as $step) {
            SchoolOnboardingProgress::updateOrCreate(['school_id' => $school->id, 'step_code' => $step], ['is_completed' => true, 'completed_at' => now()]);
        }
        $school->update(['onboarding_status' => 'completed']);
        $this->command?->warn('Seeded demo school with owner login: owner@demoschool.test / ChangeMe!123');
    }
}
