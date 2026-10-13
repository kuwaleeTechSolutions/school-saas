<?php
namespace App\Livewire\Tenant\Hr\Staff;
use App\Domain\Tenant\Models\StaffProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public function mount(): void { $this->authorize('viewAny', StaffProfile::class); }
    public function render()
    {
        $staff = StaffProfile::with('user')->orderBy('id')->paginate(15);
        return view('livewire.tenant.hr.staff.index', ['staff' => $staff]);
    }
}
