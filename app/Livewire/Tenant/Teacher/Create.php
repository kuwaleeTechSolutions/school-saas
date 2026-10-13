<?php
namespace App\Livewire\Tenant\Teacher;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $qualification = '';
    public string $specialization = '';
    public string $joiningDate = '';
    public string $employeeCode = '';

    public function mount(): void { $this->authorize('create', Teacher::class); $this->joiningDate = now()->format('Y-m-d'); }

    public function save(EntitlementServiceContract $entitlements, AuditService $audit): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'joiningDate' => ['required', 'date']]);
        if (! $entitlements->limitAvailable('teachers', 1)) {
            $this->addError('email', "Your plan's teacher limit has been reached. Please upgrade your plan.");
            return;
        }
        $user = User::create(['name' => $this->name, 'email' => $this->email, 'password' => Hash::make(Str::random(16)), 'status' => 'active']);
        $teacherRole = Role::where('name', 'Teacher')->whereNull('school_id')->first();
        if ($teacherRole) $user->assignRole($teacherRole);
        $teacher = Teacher::create([
            'user_id' => $user->id, 'employee_code' => $this->employeeCode ?: null, 'qualification' => $this->qualification ?: null,
            'specialization' => $this->specialization ?: null, 'joining_date' => $this->joiningDate, 'status' => 'active',
        ]);
        $audit->logSchool(auth()->id(), 'teacher.created', Teacher::class, $teacher->id, null, ['email' => $user->email]);
        session()->flash('success', 'Teacher added successfully.');
        $this->redirect(route('tenant.teachers.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.teacher.create'); }
}
