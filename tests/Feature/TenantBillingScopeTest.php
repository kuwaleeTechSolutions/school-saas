<?php
namespace Tests\Feature;
use App\Domain\Platform\Services\PlatformInvoiceService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Livewire\Tenant\Billing\Dashboard;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** PlatformInvoice has no tenant scope, so Billing\Dashboard's explicit school_id filter is the isolation boundary. */
class TenantBillingScopeTest extends TestCase
{
    use RefreshDatabase;
    protected function makeSchool(?string $slug = null): array
    {
        $school = School::factory()->create($slug ? ['slug' => $slug] : []);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $sub = $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return [$school, $sub];
    }

    /** @test */ public function an_owner_sees_only_their_own_schools_invoices(): void
    {
        $this->seed(); [$a, $subA] = $this->makeSchool('bill-a'); [$b, $subB] = $this->makeSchool('bill-b');
        $svc = app(PlatformInvoiceService::class);
        $invA = $svc->generateForPeriod($subA, Carbon::parse('2026-06-01')); $invB = $svc->generateForPeriod($subB, Carbon::parse('2026-06-01'));
        app(TenantContext::class)->set($a);
        $owner = User::factory()->forSchool($a)->create(); $owner->assignRole(Role::where('name','School Owner')->whereNull('school_id')->first());
        $this->actingAs($owner);
        Livewire::test(Dashboard::class)->assertSee($invA->invoice_number)->assertDontSee($invB->invoice_number);
    }
    /** @test */ public function a_non_owner_is_forbidden(): void
    {
        $this->seed(); [$s] = $this->makeSchool('bill-forbid'); app(TenantContext::class)->set($s);
        $this->actingAs(User::factory()->forSchool($s)->create());
        Livewire::test(Dashboard::class)->assertForbidden();
    }
}
