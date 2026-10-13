<?php
namespace App\Domain\Tenant\Reports;
use Illuminate\Support\Collection;
/** One report = one class. Add a report by adding a class and listing it in ReportRegistry — no other change. */
interface ReportDefinition
{
    public function key(): string;
    public function title(): string;
    /** Permission code checked through EntitlementService::can() (plan AND role). */
    public function permission(): string;
    /** @return array<string, array{label:string, type:string, options?:array<string,string>}> */
    public function filters(): array;
    /** @return array<string,string> column key => heading */
    public function columns(): array;
    /** @return array<string, mixed> Laravel validation rules for the filter input. */
    public function rules(array $input = []): array;
    /** Must only use tenant-scoped models. @return Collection<int, array<string, scalar|null>> */
    public function rows(array $filters, int $limit): Collection;
}
