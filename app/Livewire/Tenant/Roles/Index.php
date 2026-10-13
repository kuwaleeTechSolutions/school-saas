<?php
namespace App\Livewire\Tenant\Roles;
use App\Domain\Shared\Tenancy\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function mount(): void { $this->authorize('viewAny', Role::class); }
    public function render()
    {
        $school = app(TenantContext::class)->school();
        return view('livewire.tenant.roles.index', [
            'systemRoles' => Role::whereNull('school_id')->withCount('permissions')->orderBy('name')->get(),
            'customRoles' => Role::where('school_id', $school->id)->withCount('permissions')->orderBy('name')->get(),
        ]);
    }
}
