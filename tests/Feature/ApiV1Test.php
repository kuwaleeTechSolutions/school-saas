<?php
namespace Tests\Feature;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Requires: HasApiTokens on User, Sanctum migration, and `require __DIR__.'/api_v1.php'` in routes/api.php. */
class ApiV1Test extends TestCase
{
    use RefreshDatabase;
    /** @return array{0:School,1:User} school on the Enterprise plan + a School Owner logged in, tenant bound. */
    protected function makeSchool(string $slug, string $plan = 'enterprise', ?string $role = 'School Owner'): array
    {
        $school = School::factory()->create(['slug' => $slug]);
        $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>Plan::where('code',$plan)->firstOrFail()->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        app(TenantContext::class)->set($school);
        $user = User::factory()->forSchool($school)->create(['email' => $slug.'@example.test']);
        if ($role) $user->assignRole(Role::where('name', $role)->whereNull('school_id')->firstOrFail());
        return [$school, $user];
    }
    protected function addStudent(School $school, array $attrs = []): \App\Domain\Tenant\Models\Student
    {
        app(TenantContext::class)->set($school);
        return \App\Domain\Tenant\Models\Student::factory()->create(array_merge(['school_id' => $school->id], $attrs));
    }

    protected function login(string $school, string $email, string $password = 'password')
    { return $this->postJson('/api/v1/auth/token', ['school' => $school, 'email' => $email, 'password' => $password, 'device_name' => 'phone']); }
    protected function bearer(string $token): array { return ['Authorization' => 'Bearer '.$token]; }

    /** @test */ public function a_parent_logs_in_and_gets_only_the_parent_ability(): void
    {
        $this->seed(); [, $p] = $this->makeSchool('api-p', 'enterprise', 'Parent');
        $r = $this->login('api-p', $p->email); $r->assertOk(); $this->assertSame(['parent'], $r->json('abilities'));
    }
    /** @test */ public function wrong_password_and_unknown_school_get_the_same_reply(): void
    {
        $this->seed(); [, $p] = $this->makeSchool('api-x', 'enterprise', 'Parent');
        $a = $this->login('api-x', $p->email, 'wrong'); $b = $this->login('no-such-school', $p->email);
        $a->assertStatus(401); $b->assertStatus(401); $this->assertSame($a->json('message'), $b->json('message'));
    }
    /** @test */ public function login_is_rate_limited(): void
    {
        $this->seed(); [, $p] = $this->makeSchool('api-rl', 'enterprise', 'Parent');
        foreach (range(1, 5) as $i) $this->login('api-rl', $p->email, 'wrong')->assertStatus(401);
        $this->login('api-rl', $p->email, 'wrong')->assertStatus(429);
    }
    /** @test */ public function requests_without_a_token_are_rejected(): void { $this->seed(); $this->getJson('/api/v1/me')->assertStatus(401); }
    /** @test */ public function a_parent_token_cannot_reach_teacher_or_staff_endpoints(): void
    {
        $this->seed(); [, $p] = $this->makeSchool('api-ab', 'enterprise', 'Parent'); $t = $this->login('api-ab', $p->email)->json('token');
        $this->getJson('/api/v1/teacher/classes', $this->bearer($t))->assertStatus(403);
        $this->getJson('/api/v1/students', $this->bearer($t))->assertStatus(403);
    }
    /** @test */ public function a_parent_sees_only_their_own_children(): void
    {
        $this->seed(); [$school, $p] = $this->makeSchool('api-ch', 'enterprise', 'Parent');
        $mine = $this->addStudent($school, ['first_name' => 'Mine']); $other = $this->addStudent($school, ['first_name' => 'Other']);
        $g = Guardian::create(['school_id'=>$school->id,'user_id'=>$p->id,'name'=>'P','phone'=>'1']); $g->students()->attach($mine->id, ['relation'=>'father','is_primary_contact'=>true]);
        $t = $this->login('api-ch', $p->email)->json('token');
        $r = $this->getJson('/api/v1/parent/children', $this->bearer($t))->assertOk();
        $this->assertSame([$mine->ulid], array_column($r->json('data'), 'id')); $this->assertStringNotContainsString($other->ulid, $r->getContent());
    }
    /** @test */ public function the_token_fixes_the_tenant_so_a_staff_token_never_sees_another_school(): void
    {
        $this->seed(); [$a, $ownerA] = $this->makeSchool('api-sa'); $sa = $this->addStudent($a, ['first_name' => 'InA']);
        [$b] = $this->makeSchool('api-sb'); $sb = $this->addStudent($b, ['first_name' => 'InB']);
        $t = $this->login('api-sa', $ownerA->email)->json('token');
        $body = $this->getJson('/api/v1/students', $this->bearer($t))->assertOk()->getContent();
        $this->assertStringContainsString($sa->ulid, $body); $this->assertStringNotContainsString($sb->ulid, $body);
    }
    /** @test */ public function student_payloads_expose_no_contact_or_medical_fields(): void
    {
        $this->seed(); [$a, $owner] = $this->makeSchool('api-pv'); $this->addStudent($a, ['phone' => '9999999999', 'allergies' => 'peanuts']);
        $t = $this->login('api-pv', $owner->email)->json('token'); $body = $this->getJson('/api/v1/students', $this->bearer($t))->getContent();
        $this->assertStringNotContainsString('9999999999', $body); $this->assertStringNotContainsString('peanuts', $body);
    }
    /** @test */ public function a_school_with_a_lapsed_subscription_is_locked_out_of_the_api(): void
    {
        $this->seed(); [$school, $p] = $this->makeSchool('api-lock', 'enterprise', 'Parent'); $t = $this->login('api-lock', $p->email)->json('token');
        $school->subscriptions()->update(['status' => 'expired']);
        $this->getJson('/api/v1/me', $this->bearer($t))->assertStatus(403);
    }
    /** @test */ public function logout_revokes_the_token(): void
    {
        $this->seed(); [, $p] = $this->makeSchool('api-lo', 'enterprise', 'Parent'); $t = $this->login('api-lo', $p->email)->json('token');
        $this->postJson('/api/v1/auth/logout', [], $this->bearer($t))->assertOk();
        $this->getJson('/api/v1/me', $this->bearer($t))->assertStatus(401);
    }
}
