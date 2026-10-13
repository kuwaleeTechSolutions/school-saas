<?php
namespace App\Livewire\Tenant\Reports;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Reports\ReportRegistry;
use App\Domain\Tenant\Reports\ReportRunner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $key = '';
    public array $filters = [];
    public ?array $preview = null;
    public bool $truncated = false;
    public function updatedKey(): void { $this->filters = []; $this->preview = null; }
    public function run(ReportRunner $runner): void
    {
        $this->resetErrorBag();
        try {
            $r = $runner->run($this->key, array_filter($this->filters, fn ($v) => $v !== null && $v !== ''), auth()->user(), 100);
            $this->preview = $r->rows; $this->truncated = $r->truncated;
        } catch (AuthorizationException) { $this->addError('key', 'You are not allowed to run this report.'); $this->preview = null; }
        catch (ValidationException $e) { foreach ($e->errors() as $f => $m) $this->addError('filters.'.$f, $m[0]); $this->preview = null; }
        catch (\InvalidArgumentException) { $this->addError('key', 'Choose a report.'); $this->preview = null; }
    }
    public function render(ReportRegistry $registry, EntitlementServiceContract $entitlements)
    {
        $available = $registry->availableFor($entitlements);
        $current = $available[$this->key] ?? null;
        return view('livewire.tenant.reports.index', ['available' => $available, 'current' => $current,
            'exportQuery' => http_build_query(array_filter($this->filters, fn ($v) => $v !== null && $v !== ''))]);
    }
}
