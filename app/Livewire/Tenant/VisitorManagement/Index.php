<?php
namespace App\Livewire\Tenant\VisitorManagement;
use App\Domain\Tenant\Models\VisitorLog;
use App\Domain\Tenant\Services\VisitorService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $visitorName = '';
    public string $visitorPhone = '';
    public string $purpose = '';
    public string $whomToMeet = '';

    public function mount(): void { $this->authorize('viewAny', VisitorLog::class); }

    public function checkIn(VisitorService $visitorService): void
    {
        $this->authorize('create', VisitorLog::class);
        $this->validate(['visitorName' => ['required', 'string', 'max:255'], 'purpose' => ['required', 'string', 'max:255']]);
        $visitorService->checkIn($this->visitorName, $this->visitorPhone ?: null, $this->purpose, $this->whomToMeet ?: null, auth()->user());
        $this->reset(['visitorName', 'visitorPhone', 'purpose', 'whomToMeet']);
        session()->flash('success', 'Visitor checked in.');
    }

    public function checkOut(int $logId, VisitorService $visitorService): void
    {
        $log = VisitorLog::findOrFail($logId);
        $this->authorize('update', $log);
        try {
            $visitorService->checkOut($log, auth()->user());
            session()->flash('success', 'Visitor checked out.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tenant.visitor-management.index', [
            'currentlyIn' => VisitorLog::whereNull('checked_out_at')->orderByDesc('checked_in_at')->get(),
            'recentHistory' => VisitorLog::whereNotNull('checked_out_at')->orderByDesc('checked_out_at')->limit(20)->get(),
        ]);
    }
}
