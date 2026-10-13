<?php
namespace App\Domain\Platform\Services;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolUsageCounter;
use Illuminate\Support\Collection;
/**
 * Read-only aggregation over Stage 1's school_usage_counters + plan_limits. This is a PLATFORM-level service
 * (it spans schools), so it reads counters with withoutGlobalScopes() and an explicit school_id filter —
 * it must not depend on a bound TenantContext.
 */
class UsageMeteringService
{
    protected const METRICS = ['students','teachers','staff','storage_mb'];
    /** @return Collection<int, array{metric:string, used:int, limit:?int, percent:?float, is_near_limit:bool}> */
    public function usageFor(School $school): Collection
    {
        $plan = $school->activeSubscription()->first()?->plan;
        return collect(self::METRICS)->map(function (string $metric) use ($school, $plan) {
            $used = (int) SchoolUsageCounter::withoutGlobalScopes()->where('school_id', $school->id)->where('metric', $metric)->value('current_value');
            $limit = $plan?->limits()->where('metric', $metric)->value('max_value');
            $limit = $limit !== null ? (int) $limit : null;
            $percent = ($limit !== null && $limit > 0) ? round(($used / $limit) * 100, 1) : null;
            return ['metric'=>$metric,'used'=>$used,'limit'=>$limit,'percent'=>$percent,'is_near_limit'=>$percent !== null && $percent >= 90];
        });
    }
    public function schoolsNearingLimits(): Collection
    {
        return School::where('status', 'active')->get()->filter(fn (School $s) => $this->usageFor($s)->contains(fn ($r) => $r['is_near_limit']))->values();
    }
    public function platformTotals(): array
    {
        $t = [];
        foreach (self::METRICS as $m) $t[$m] = (int) SchoolUsageCounter::withoutGlobalScopes()->where('metric', $m)->sum('current_value');
        return $t;
    }
}
