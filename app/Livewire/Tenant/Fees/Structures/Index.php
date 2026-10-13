<?php
namespace App\Livewire\Tenant\Fees\Structures;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\FeeStructure;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function mount(): void { $this->authorize('viewAny', FeeStructure::class); }
    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();
        $structures = $session ? FeeStructure::where('academic_session_id', $session->id)->with(['schoolClass', 'section', 'items.feeComponent'])->orderBy('name')->get() : collect();
        return view('livewire.tenant.fees.structures.index', ['structures' => $structures]);
    }
}
