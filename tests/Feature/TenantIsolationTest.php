<?php
namespace Tests\Feature;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Livewire\Tenant\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;


class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    protected School $schoolA; protected School $schoolB; protected User $userA; protected User $userB;
    protected function setUp(): void
    {
        parent::setUp(); $this->seed();
        $this->schoolA = School::factory()->create(['slug'=>'school-a-test']);
        $this->schoolB = School::factory()->create(['slug'=>'school-b-test']);
        $this->userA = User::factory()->forSchool($this->schoolA)->create(['email'=>'usera@school-a-test.example','password'=>Hash::make('password')]);
        $this->userB = User::factory()->forSchool($this->schoolB)->create(['email'=>'userb@school-b-test.example','password'=>Hash::make('password')]);
    }
    protected function bindTenant(School $school): void { app(TenantContext::class)->set($school); URL::defaults(['school_slug' => $school->slug]); }
    /** @test */
    public function query_scoping_hides_the_other_schools_users(): void
    {
        $this->bindTenant($this->schoolA);
        $visibleIds = User::pluck('id')->all();
        $this->assertContains($this->userA->id, $visibleIds);
        $this->assertNotContains($this->userB->id, $visibleIds);
    }
    /** @test */
    public function find_by_id_returns_null_across_tenants(): void
    {
        $this->bindTenant($this->schoolA);
        $this->assertNull(User::find($this->userB->id));
    }
    /** @test */
    /** @test */
    public function queries_with_no_bound_tenant_fail_closed(): void
    {    app(TenantContext::class)->clear();

        $this->assertCount(0, User::all());
    }
    /** @test */
    public function login_component_rejects_another_schools_credentials(): void
    {
        $this->bindTenant($this->schoolB);
        Livewire::test(Login::class)->set('email', $this->userA->email)->set('password', 'password')->call('login')->assertHasErrors('email');
        $this->assertGuest();
    }
    /** @test */
    public function an_authenticated_session_on_one_subdomain_does_not_grant_access_on_another(): void
    {
        $this->bindTenant($this->schoolB);
        Livewire::test(Login::class)->set('email', $this->userB->email)->set('password', 'password')->call('login')->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticatedAs($this->userB);
        $hostA = "school-a-test.{$this->tenantSuffix()}";
        $response = $this->get("http://{$hostA}/dashboard");
        $this->assertGuest();
        $response->assertRedirect("http://{$hostA}/login");
    }
    protected function tenantSuffix(): string { return ltrim(config('platform.tenant_domain_suffix', '.localhost'), '.'); }
}
