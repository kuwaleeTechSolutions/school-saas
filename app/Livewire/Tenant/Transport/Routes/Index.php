<?php
namespace App\Livewire\Tenant\Transport\Routes;
use App\Domain\Tenant\Models\TransportRoute;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $name = '';
    public string $vehicleNumber = '';
    public string $driverName = '';
    public string $driverPhone = '';
    public float $monthlyFee = 0;

    public function mount(): void { $this->authorize('viewAny', TransportRoute::class); }

    public function addRoute(): void
    {
        $this->authorize('create', TransportRoute::class);
        $this->validate(['name' => ['required', 'string', 'max:255'], 'monthlyFee' => ['required', 'numeric', 'min:0']]);
        TransportRoute::create(['name' => $this->name, 'vehicle_number' => $this->vehicleNumber ?: null, 'driver_name' => $this->driverName ?: null, 'driver_phone' => $this->driverPhone ?: null, 'monthly_fee' => $this->monthlyFee, 'is_active' => true]);
        $this->reset(['name', 'vehicleNumber', 'driverName', 'driverPhone', 'monthlyFee']);
        session()->flash('success', 'Route added.');
    }

    public function render() { return view('livewire.tenant.transport.routes.index', ['routes' => TransportRoute::withCount('assignments')->orderBy('name')->get()]); }
}
