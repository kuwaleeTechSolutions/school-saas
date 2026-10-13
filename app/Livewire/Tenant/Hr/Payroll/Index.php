<?php
namespace App\Livewire\Tenant\Hr\Payroll;
use App\Domain\Tenant\Models\PayrollRun;
use App\Domain\Tenant\Services\PayrollService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public int $year;
    public int $month;

    public function mount(): void { $this->authorize('viewAny', PayrollRun::class); $this->year = (int) now()->format('Y'); $this->month = (int) now()->format('n'); }

    public function generate(PayrollService $payrollService): void
    {
        $this->authorize('create', PayrollRun::class);
        try {
            $payrollService->generate($this->year, $this->month, auth()->user());
            session()->flash('success', 'Payroll run generated as draft.');
        } catch (RuntimeException $e) {
            $this->addError('month', $e->getMessage());
        }
    }

    public function finalize(int $runId, PayrollService $payrollService): void
    {
        $run = PayrollRun::findOrFail($runId);
        $this->authorize('finalize', $run);
        try {
            $payrollService->finalize($run, auth()->user());
            session()->flash('success', 'Payroll run finalized. Payslip PDFs are being generated.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render() { return view('livewire.tenant.hr.payroll.index', ['runs' => PayrollRun::withCount('payslips')->orderByDesc('year')->orderByDesc('month')->get()]); }
}
