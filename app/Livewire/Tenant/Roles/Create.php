<?php
namespace App\Livewire\Tenant\Roles;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Permission\Contracts\PermissionServiceContract;
use App\Domain\Shared\Tenancy\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $name = '';
    public array $selectedPermissionIds = [];
    public function mount(): void { $this->authorize('create', Role::class); }

    public function save(PermissionServiceContract $permissions, AuditService $audit): void
    {
        $this->validate(['name' => ['required', 'string', 'max:100']]);
        $school = app(TenantContext::class)->school();
        $allowed = $permissions->assignablePermissionsFor($school)->pluck('id')->all();
        $toAssign = array_values(array_intersect($this->selectedPermissionIds, $allowed));
        $role = Role::create(['name' => $this->name, 'guard_name' => 'web', 'school_id' => $school->id]);
        $role->syncPermissions($toAssign);
        $audit->logSchool(auth()->id(), 'role.created', Role::class, $role->id, null, ['name' => $role->name, 'permission_count' => count($toAssign)]);
        session()->flash('success', 'Custom role created.');
        $this->redirect(route('tenant.roles.index'), navigate: true);
    }

    public function render(PermissionServiceContract $permissions)
    {
        $school = app(TenantContext::class)->school();
        $available = $permissions->assignablePermissionsFor($school)->load(['module', 'feature']);
        return view('livewire.tenant.roles.create', ['availablePermissions' => $available->groupBy(fn($p) => $p->module?->name ?? 'General')]);
    }
}
