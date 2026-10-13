<?php
namespace App\Livewire\Tenant\Hr\Staff;
use App\Domain\Tenant\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $employeeCode = '';
    public string $designation = '';
    public string $department = '';
    public string $joiningDate = '';
    public float $monthlyGrossSalary = 0;
    public int $annualLeaveQuota = 12;

    public function mount(): void { $this->authorize('create', StaffProfile::class); $this->joiningDate = now()->format('Y-m-d'); }

    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'monthlyGrossSalary' => ['required', 'numeric', 'min:0']]);
        $user = User::create(['name' => $this->name, 'email' => $this->email, 'password' => Hash::make(Str::random(16)), 'status' => 'active']);
        StaffProfile::create(['user_id' => $user->id, 'employee_code' => $this->employeeCode ?: null, 'designation' => $this->designation ?: null, 'department' => $this->department ?: null, 'joining_date' => $this->joiningDate, 'monthly_gross_salary' => $this->monthlyGrossSalary, 'annual_leave_quota' => $this->annualLeaveQuota, 'status' => 'active']);
        session()->flash('success', 'Staff member added.');
        $this->redirect(route('tenant.hr.staff.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.hr.staff.create'); }
}
