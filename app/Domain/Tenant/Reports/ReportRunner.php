<?php
namespace App\Domain\Tenant\Reports;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
/**
 * The single path for running/exporting reports. Authorisation is re-checked HERE on every run and export
 * (never trusted from the UI), filters are validated against the report's own rules and reduced to declared
 * keys, and output is hard-capped. Reports read only tenant-scoped models, so isolation comes from the models.
 */
class ReportRunner
{
    public const MAX_ROWS = 5000;
    public function __construct(protected ReportRegistry $registry, protected EntitlementServiceContract $entitlements, protected AuditService $audit) {}

    public function run(string $key, array $input, User $user, ?int $limit = null): ReportResult
    {
        $def = $this->registry->find($key) ?? throw new InvalidArgumentException("Unknown report '{$key}'.");
        if (! $this->entitlements->can($def->permission())) throw new AuthorizationException('You are not allowed to run this report.');
        $filters = Validator::make($input, $def->rules($input))->validate();
        $filters = array_intersect_key($filters, $def->filters());
        $max = min($limit ?? self::MAX_ROWS, self::MAX_ROWS);
        $rows = $def->rows($filters, $max + 1)->values()->all();
        $truncated = count($rows) > $max;
        $this->audit->logSchool($user->id, 'report.run', null, null, null, ['report' => $key, 'filters' => $filters, 'rows' => min(count($rows), $max)]);
        return new ReportResult($def, $filters, array_slice($rows, 0, $max), $truncated);
    }

    public function toCsv(ReportResult $r): string
    {
        $h = fopen('php://temp', 'w+');
        fputcsv($h, array_values($r->definition->columns()));
        foreach ($r->rows as $row) {
            $line = [];
            foreach (array_keys($r->definition->columns()) as $col) $line[] = self::safeCell($row[$col] ?? '');
            fputcsv($h, $line);
        }
        rewind($h); $csv = stream_get_contents($h); fclose($h);
        return $csv;
    }

    /** Spreadsheet formula-injection guard: a text cell starting with = + - @ (or tab/CR) is neutralised. */
    public static function safeCell(mixed $v): mixed
    {
        if (is_string($v) && $v !== '' && preg_match('/^[=+\-@\t\r]/', $v)) return "'".$v;
        return $v;
    }
}
