<?php
namespace Tests\Feature;
use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\AttendanceSession;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
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

    protected function structure(School $school): array
    {
        app(TenantContext::class)->set($school);
        $session = AcademicSession::factory()->current()->create(['school_id' => $school->id]);
        $session->markAsCurrent();
        $class = SchoolClass::factory()->create(['school_id' => $school->id, 'academic_session_id' => $session->id, 'name' => 'Grade '.Str::random(3)]);
        $section = Section::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        return [$session, $class, $section];
    }
    /** @test */ public function attendance_trend_computes_the_daily_percentage(): void
    {
        $this->seed(); Cache::flush(); [$school, $u] = $this->makeSchool('an-att'); [$session, $class, $section] = $this->structure($school);
        $s1 = $this->addStudent($school); $s2 = $this->addStudent($school);
        $as = AttendanceSession::create(['academic_session_id'=>$session->id,'class_id'=>$class->id,'section_id'=>$section->id,'attendance_date'=>now()->toDateString(),'period_number'=>0,'taken_by'=>$u->id]);
        AttendanceRecord::create(['attendance_session_id'=>$as->id,'student_id'=>$s1->id,'status'=>'present']);
        AttendanceRecord::create(['attendance_session_id'=>$as->id,'student_id'=>$s2->id,'status'=>'absent']);
        $trend = app(AnalyticsService::class)->attendanceTrend(7);
        $this->assertCount(1, $trend); $this->assertEquals(50.0, $trend[0]['percent']); $this->assertSame(2, $trend[0]['marked']);
    }
    /** @test */ public function student_distribution_counts_active_enrollments_per_class(): void
    {
        $this->seed(); Cache::flush(); [$school] = $this->makeSchool('an-dist'); [$session, $class, $section] = $this->structure($school);
        foreach (range(1, 2) as $i) $this->addStudent($school)->enrollments()->create(['school_id'=>$school->id,'academic_session_id'=>$session->id,'class_id'=>$class->id,'section_id'=>$section->id,'status'=>'active']);
        $this->assertSame([$class->name => 2], app(AnalyticsService::class)->studentDistribution());
    }
    /** @test */ public function the_cache_never_leaks_between_schools(): void
    {
        $this->seed(); Cache::flush();
        [$a] = $this->makeSchool('an-ca'); [$sa, $ca, $seca] = $this->structure($a); $this->addStudent($a)->enrollments()->create(['school_id'=>$a->id,'academic_session_id'=>$sa->id,'class_id'=>$ca->id,'section_id'=>$seca->id,'status'=>'active']);
        $first = app(AnalyticsService::class)->studentDistribution(); $this->assertNotEmpty($first);
        [$b] = $this->makeSchool('an-cb'); $this->structure($b);
        $this->assertSame([], app(AnalyticsService::class)->studentDistribution(), 'School B must not see School A\'s cached figures.');
    }
}
