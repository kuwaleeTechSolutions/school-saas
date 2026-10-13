<?php
namespace App\Livewire\Tenant\Hr\Payroll;
use App\Domain\Tenant\Models\PayrollRun;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public PayrollRun $run;
    public function mount(PayrollRun $run): void { $this->authorize('viewAny', PayrollRun::class); $this->run = $run; }
    public function render() { $this->run->load('payslips.staffProfile.user'); return view('livewire.tenant.hr.payroll.show'); }
}
