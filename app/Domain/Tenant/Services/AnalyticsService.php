<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\AdmissionApplication;
use App\Domain\Tenant\Models\AttendanceSession;
use App\Domain\Tenant\Models\FeePayment;
use App\Domain\Tenant\Models\StudentEnrollment;
use Illuminate\Support\Facades\Cache;
/**
 * Read-only aggregates over existing tenant-scoped tables. Every cache key includes the school id, so a cache hit
 * can never serve one school's numbers to another. Results are cached 10 minutes — figures can lag slightly.
 */
class AnalyticsService
{
    public const TTL = 600;
    public function __construct(protected TenantContext $tenant) {}

    /** @return array<int, array{date:string, attended:int, marked:int, percent:?float}> daily-mode sessions only */
    public function attendanceTrend(int $days = 14): array
    {
        return $this->remember("attendance:{$days}", function () use ($days) {
            $from = now()->subDays($days - 1)->toDateString();
            return AttendanceSession::where('period_number', 0)->where('attendance_date', '>=', $from)
                ->with('records:id,attendance_session_id,status')->get()->groupBy(fn ($s) => $s->attendance_date->toDateString())->sortKeys()
                ->map(function ($sessions, $date) {
                    $recs = $sessions->flatMap->records;
                    $attended = $recs->whereIn('status', ['present', 'late', 'half_day'])->count(); $marked = $recs->count();
                    return ['date' => $date, 'attended' => $attended, 'marked' => $marked, 'percent' => $marked ? round($attended / $marked * 100, 1) : null];
                })->values()->all();
        });
    }

    /** @return array<string, float> 'YYYY-MM' => successful payments collected */
    public function feeCollectionByMonth(int $months = 6): array
    {
        return $this->remember("fees:{$months}", function () use ($months) {
            $start = now()->subMonths($months - 1)->startOfMonth();
            return FeePayment::where('status', 'success')->where('paid_at', '>=', $start)->get(['amount', 'paid_at'])
                ->groupBy(fn ($p) => $p->paid_at->format('Y-m'))->map(fn ($g) => round((float) $g->sum('amount'), 2))->sortKeys()->all();
        });
    }

    /** @return array<string, int> class name => active enrolled students in the current session */
    public function studentDistribution(): array
    {
        return $this->remember('distribution', function () {
            $session = $this->tenant->school()->activeAcademicSession();
            if (! $session) return [];
            return StudentEnrollment::where('academic_session_id', $session->id)->where('status', 'active')->with('schoolClass:id,name')->get()
                ->groupBy(fn ($e) => $e->schoolClass->name)->map->count()->sortKeys()->all();
        });
    }

    /** @return array<string, int> admission stage => applications */
    public function admissionsFunnel(): array
    {
        return $this->remember('admissions', fn () => AdmissionApplication::selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage')->map(fn ($v) => (int) $v)->all());
    }

    protected function remember(string $suffix, \Closure $fn): array
    {
        return Cache::remember('analytics:'.$this->tenant->school()->id.':'.$suffix, self::TTL, $fn);
    }
}
