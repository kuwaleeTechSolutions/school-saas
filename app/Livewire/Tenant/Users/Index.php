<?php
namespace App\Livewire\Tenant\Users;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public string $newName = '';
    public string $newEmail = '';
    public ?int $newRoleId = null;

    public function mount(): void { $this->authorize('viewAny', User::class); }

    public function invite(EntitlementServiceContract $entitlements, AuditService $audit): void
    {
        $this->authorize('create', User::class);
        $this->validate(['newName' => ['required', 'string', 'max:255'], 'newEmail' => ['required', 'email', 'unique:users,email'], 'newRoleId' => ['required', 'exists:roles,id']]);
        if (! $entitlements->limitAvailable('admins', 1)) {
            $this->addError('newEmail', "Your plan's admin/staff limit has been reached.");
            return;
        }
        $user = User::create(['name' => $this->newName, 'email' => $this->newEmail, 'password' => Hash::make(Str::random(16)), 'status' => 'active']);
        $role = Role::findOrFail($this->newRoleId);
        $user->assignRole($role);
        $audit->logSchool(auth()->id(), 'user.created', User::class, $user->id, null, ['email' => $user->email, 'role' => $role->name]);
        $this->reset(['newName', 'newEmail', 'newRoleId']);
        session()->flash('success', 'User invited successfully.');
    }

    public function render()
    {
        $users = User::query()->with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')->paginate(15);
        $assignableRoles = app(\App\Domain\Shared\Permission\Contracts\PermissionServiceContract::class)
            ->assignableRolesFor(app(\App\Domain\Shared\Tenancy\TenantContext::class)->school());
        return view('livewire.tenant.users.index', ['users' => $users, 'assignableRoles' => $assignableRoles]);
    }
}
