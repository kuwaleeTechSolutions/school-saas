<?php
namespace Tests\Feature;
use App\Domain\Shared\Ai\AiAssistantService;
use App\Domain\Shared\Ai\AiProviderContract;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiAssistantTest extends TestCase
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

    protected function fakeProvider(): void
    {
        $this->app->bind(AiProviderContract::class, fn () => new class implements AiProviderContract {
            public array $seen = [];
            public function isConfigured(): bool { return true; }
            public function complete(array $messages): string { return 'canned answer'; }
        });
    }
    /** @test */ public function by_default_it_refuses_honestly_because_no_provider_is_connected(): void
    {
        $this->seed(); [, $u] = $this->makeSchool('ai-off'); $this->actingAs($u);
        $this->expectException(RuntimeException::class); app(AiAssistantService::class)->ask($u, 'hello');
    }
    /** @test */ public function a_user_without_the_permission_is_refused_before_the_provider_is_touched(): void
    {
        $this->seed(); $this->fakeProvider(); [, $u] = $this->makeSchool('ai-noperm', 'enterprise', null); $this->actingAs($u);
        $this->expectException(AuthorizationException::class); app(AiAssistantService::class)->ask($u, 'hello');
    }
    /** @test */ public function an_allowed_user_gets_an_answer_and_the_audit_log_holds_no_text(): void
    {
        $this->seed(); $this->fakeProvider(); [$school, $u] = $this->makeSchool('ai-ok'); $this->actingAs($u); RateLimiter::clear('ai-ask:'.$u->id);
        $this->assertSame('canned answer', app(AiAssistantService::class)->ask($u, 'secret question text'));
        $log = $school->auditLogs()->where('action', 'ai.asked')->first(); $this->assertNotNull($log);
        $this->assertStringNotContainsString('secret question text', json_encode($log->new_values)); $this->assertStringNotContainsString('canned answer', json_encode($log->new_values));
    }
    /** @test */ public function it_is_rate_limited_per_user(): void
    {
        $this->seed(); $this->fakeProvider(); [, $u] = $this->makeSchool('ai-rl'); $this->actingAs($u); RateLimiter::clear('ai-ask:'.$u->id);
        $svc = app(AiAssistantService::class); foreach (range(1, 20) as $i) $svc->ask($u, 'q');
        $this->expectException(RuntimeException::class); $svc->ask($u, 'one too many');
    }
}
