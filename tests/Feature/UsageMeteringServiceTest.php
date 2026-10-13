<?php
namespace Tests\Feature;
use App\Domain\Platform\Services\UsageMeteringService;
use App\Domain\Tenant\Models\SchoolUsageCounter;
use App\Domain\Platform\Models\Plan;
use App\Domain\Tenant\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/** Pure read-side aggregation; works with NO bound tenant (platform-level service). */
class UsageMeteringServiceTest extends TestCase
{
    use RefreshDatabase;
    protected School $school;
    protected function setUp(): void
    {
        parent::setUp(); $this->seed(); [$this->school, $sub] = $this->makeSchool();
        $sub->plan->limits()->updateOrCreate(['metric'=>'students'], ['max_value'=>100]);
    }
    protected function makeSchool(?string $slug = null): array
    {
        $school = School::factory()->create($slug ? ['slug' => $slug] : []);
        $plan = Plan::where('code', 'professional')->firstOrFail();
        $sub = $school->subscriptions()->create(['ulid'=>(string) Str::ulid(),'plan_id'=>$plan->id,'billing_cycle'=>'monthly','status'=>'active','current_period_starts_at'=>now(),'current_period_ends_at'=>now()->addMonth(),'is_active'=>true]);
        return [$school, $sub];
    }

    protected function counter(School $s, string $m, int $v): void { SchoolUsageCounter::withoutGlobalScopes()->create(['school_id'=>$s->id,'metric'=>$m,'current_value'=>$v]); }
    /** @test */ public function percent_is_computed(): void { $this->counter($this->school,'students',50); $r = app(UsageMeteringService::class)->usageFor($this->school)->firstWhere('metric','students'); $this->assertEquals(50.0, $r['percent']); $this->assertFalse($r['is_near_limit']); }
    /** @test */ public function ninety_percent_is_near_limit(): void { $this->counter($this->school,'students',91); $this->assertTrue(app(UsageMeteringService::class)->usageFor($this->school)->firstWhere('metric','students')['is_near_limit']); }
    /** @test */ public function no_limit_means_null_percent_and_never_near_limit(): void
    {
        $this->school->activeSubscription()->first()->plan->limits()->where('metric','teachers')->delete(); $this->counter($this->school,'teachers',9999);
        $r = app(UsageMeteringService::class)->usageFor($this->school)->firstWhere('metric','teachers'); $this->assertNull($r['limit']); $this->assertNull($r['percent']); $this->assertFalse($r['is_near_limit']);
    }
    /** @test */ public function only_schools_at_or_above_ninety_percent_are_listed(): void
    {
        $this->counter($this->school,'students',95); [$other] = $this->makeSchool(); $this->counter($other,'students',10);
        $r = app(UsageMeteringService::class)->schoolsNearingLimits(); $this->assertTrue($r->contains('id',$this->school->id)); $this->assertFalse($r->contains('id',$other->id));
    }
    /** @test */ public function platform_totals_sum_across_schools(): void
    { $this->counter($this->school,'students',10); [$o] = $this->makeSchool(); $this->counter($o,'students',5); $this->assertSame(15, app(UsageMeteringService::class)->platformTotals()['students']); }
}
