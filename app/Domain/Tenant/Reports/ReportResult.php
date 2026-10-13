<?php
namespace App\Domain\Tenant\Reports;
final class ReportResult
{
    /** @param array<int, array<string, mixed>> $rows */
    public function __construct(public readonly ReportDefinition $definition, public readonly array $filters, public readonly array $rows, public readonly bool $truncated) {}
}
