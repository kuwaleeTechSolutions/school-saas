<?php
namespace App\Domain\Tenant\Reports;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Reports\Definitions\AttendanceSummaryReport;
use App\Domain\Tenant\Reports\Definitions\FeeOutstandingReport;
use App\Domain\Tenant\Reports\Definitions\StudentListReport;
class ReportRegistry
{
    /** @return array<string, ReportDefinition> */
    public function all(): array
    {
        $defs = [new StudentListReport(), new FeeOutstandingReport(), new AttendanceSummaryReport()];
        $out = [];
        foreach ($defs as $d) $out[$d->key()] = $d;
        return $out;
    }
    public function find(string $key): ?ReportDefinition { return $this->all()[$key] ?? null; }
    /** Only the reports this user's plan AND role allow. */
    public function availableFor(EntitlementServiceContract $entitlements): array
    {
        return array_filter($this->all(), fn (ReportDefinition $d) => $entitlements->can($d->permission()));
    }
}
