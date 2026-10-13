<?php
namespace Tests\Feature;
use App\Domain\Tenant\Reports\ReportRunner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use App\Domain\Platform\Models\Plan;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Reports: permission re-check, filter validation, row cap, CSV injection guard, tenant isolation. */
class ReportRunnerTest extends TestCase
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

    /** @test */ public function it_returns_only_the_current_schools_rows(): void
    {
        $this->seed(); [$a, $ownerA] = $this->makeSchool('rep-a'); $this->addStudent($a, ['first_name' => 'Alice']);
        [$b] = $this->makeSchool('rep-b'); $this->addStudent($b, ['first_name' => 'Bob']);
        app(TenantContext::class)->set($a); $this->actingAs($ownerA);
        $names = array_column(app(ReportRunner::class)->run('students', [], $ownerA)->rows, 'name');
        $this->assertTrue(collect($names)->contains(fn ($n) => str_contains($n, 'Alice'))); $this->assertFalse(collect($names)->contains(fn ($n) => str_contains($n, 'Bob')));
    }
    /** @test */ public function a_user_without_the_permission_is_refused(): void
    {
        $this->seed(); [, $nobody] = $this->makeSchool('rep-noperm', 'enterprise', null); $this->actingAs($nobody);
        $this->expectException(AuthorizationException::class); app(ReportRunner::class)->run('students', [], $nobody);
    }
    /** @test */ public function the_plan_gate_applies_even_to_a_privileged_role(): void
    {
        $this->seed(); [, $owner] = $this->makeSchool('rep-starter', 'starter'); $this->actingAs($owner);
        $this->expectException(AuthorizationException::class); app(ReportRunner::class)->run('fees_outstanding', [], $owner);
    }
    /** @test */ public function invalid_filters_are_rejected(): void
    {
        $this->seed(); [, $owner] = $this->makeSchool('rep-bad'); $this->actingAs($owner);
        $this->expectException(ValidationException::class); app(ReportRunner::class)->run('students', ['status' => 'hacked'], $owner);
    }
    /** @test */ public function an_attendance_range_over_92_days_is_rejected(): void
    {
        $this->seed(); [, $owner] = $this->makeSchool('rep-range'); $this->actingAs($owner);
        $this->expectException(ValidationException::class); app(ReportRunner::class)->run('attendance_summary', ['from' => '2026-01-01', 'to' => '2026-12-31'], $owner);
    }
    /** @test */ public function output_is_capped_and_flagged_truncated(): void
    {
        $this->seed(); [$s, $owner] = $this->makeSchool('rep-cap'); foreach (range(1, 3) as $i) $this->addStudent($s);
        app(TenantContext::class)->set($s); $this->actingAs($owner);
        $r = app(ReportRunner::class)->run('students', [], $owner, 2); $this->assertCount(2, $r->rows); $this->assertTrue($r->truncated);
    }
    /** @test */ public function csv_neutralises_spreadsheet_formulas(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", ReportRunner::safeCell('=HYPERLINK("x")'));
        $this->assertSame("'+1", ReportRunner::safeCell('+1')); $this->assertSame("'@cmd", ReportRunner::safeCell('@cmd'));
        $this->assertSame('Alice', ReportRunner::safeCell('Alice')); $this->assertSame(-5.0, ReportRunner::safeCell(-5.0));
    }
    /** @test */ public function the_csv_contains_a_header_row_and_data(): void
    {
        $this->seed(); [$s, $owner] = $this->makeSchool('rep-csv'); $this->addStudent($s, ['first_name' => '=EVIL']);
        app(TenantContext::class)->set($s); $this->actingAs($owner);
        $runner = app(ReportRunner::class); $csv = $runner->toCsv($runner->run('students', [], $owner));
        $this->assertStringContainsString('Admission #', $csv); $this->assertStringContainsString("'=EVIL", $csv);
    }
}
