<?php
namespace Tests\Unit;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EntitlementServiceTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(); }
    protected function makeSchoolOnPlan(string $planCode): School
    {
        $school = School::factory()->create();
        $plan = Plan::where('code', $planCode)->firstOrFail();
        $school->subscriptions()->create(['ulid'=>(string) \Illuminate\Support\Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return $school->refresh();
    }
    /** @test */
    public function a_module_outside_the_plan_is_not_enabled(): void
    {
        $school = $this->makeSchoolOnPlan('starter'); app(TenantContext::class)->set($school);
        $entitlements = app(EntitlementServiceContract::class);
        $this->assertTrue($entitlements->moduleEnabled('students'));
        $this->assertFalse($entitlements->moduleEnabled('hr'));
    }
    /** @test */
    public function fees_refund_is_withheld_until_enterprise(): void
    {
        foreach (['starter','professional'] as $planCode) {
            $school = $this->makeSchoolOnPlan($planCode); app(TenantContext::class)->set($school);
            $entitlements = app(EntitlementServiceContract::class);
            $this->assertFalse($entitlements->permissionAllowedByPlan('fees.refund'));
        }
        $school = $this->makeSchoolOnPlan('enterprise'); app(TenantContext::class)->set($school);
        $entitlements = app(EntitlementServiceContract::class);
        $this->assertTrue($entitlements->permissionAllowedByPlan('fees.refund'));
    }
    /** @test */
    public function a_custom_role_permission_is_inert_if_the_plan_excludes_it(): void
    {
        $school = $this->makeSchoolOnPlan('starter'); app(TenantContext::class)->set($school);
        $user = User::factory()->forSchool($school)->create();
        $customRole = Role::create(['name'=>'Rogue Accountant','guard_name'=>'web','school_id'=>$school->id]);
        $customRole->givePermissionTo('fees.refund'); $user->assignRole($customRole);
        $this->actingAs($user);
        $entitlements = app(EntitlementServiceContract::class);
        $this->assertTrue($user->can('fees.refund'));
        $this->assertFalse($entitlements->can('fees.refund'));
    }
    /** @test */
    public function limit_available_treats_null_as_unlimited(): void
    {
        $enterprise = $this->makeSchoolOnPlan('enterprise'); app(TenantContext::class)->set($enterprise);
        $entitlements = app(EntitlementServiceContract::class);
        $this->assertNull($entitlements->remaining('students'));
        $this->assertTrue($entitlements->limitAvailable('students', 999999));
    }
    /** @test */
    public function limit_available_respects_a_finite_plan_limit(): void
    {
        $starter = $this->makeSchoolOnPlan('starter'); app(TenantContext::class)->set($starter);
        $entitlements = app(EntitlementServiceContract::class);
        $this->assertSame(500, $entitlements->remaining('students'));
        $this->assertTrue($entitlements->limitAvailable('students', 500));
        $this->assertFalse($entitlements->limitAvailable('students', 501));
        $starter->usageCounters()->create(['metric'=>'students','current_value'=>450]);
        $this->assertSame(50, $entitlements->remaining('students'));
    }
}
