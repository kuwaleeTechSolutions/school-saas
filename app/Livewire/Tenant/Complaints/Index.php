<?php
namespace App\Livewire\Tenant\Complaints;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Complaint;
use App\Domain\Tenant\Services\ComplaintService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $subject = '';
    public string $description = '';
    public string $category = 'other';
    public ?int $resolvingComplaintId = null;
    public string $resolutionNotes = '';

    public function mount(): void { $this->authorize('viewAny', Complaint::class); }

    public function raise(): void
    {
        $this->authorize('create', Complaint::class);
        $this->validate(['subject' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'max:2000']]);
        Complaint::create(['subject' => $this->subject, 'description' => $this->description, 'category' => $this->category, 'status' => 'open', 'raised_by' => auth()->id()]);
        $this->reset(['subject', 'description']);
        session()->flash('success', 'Complaint raised.');
    }

    public function transitionComplaint(int $complaintId, string $toStatus, ComplaintService $complaintService): void
    {
        $complaint = Complaint::findOrFail($complaintId);
        $this->authorize('update', $complaint);
        try {
            if ($toStatus === 'resolved') { $this->resolvingComplaintId = $complaintId; return; }
            $complaintService->transition($complaint, $toStatus, auth()->user());
            session()->flash('success', "Complaint moved to '{$toStatus}'.");
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function confirmResolve(ComplaintService $complaintService): void
    {
        $this->validate(['resolutionNotes' => ['required', 'string', 'max:1000']]);
        $complaint = Complaint::findOrFail($this->resolvingComplaintId);
        $complaintService->transition($complaint, 'resolved', auth()->user(), $this->resolutionNotes);
        $this->reset(['resolvingComplaintId', 'resolutionNotes']);
        session()->flash('success', 'Complaint resolved.');
    }

    public function render(ComplaintService $complaintService, EntitlementServiceContract $entitlements)
    {
        $canSeeAll = $entitlements->can('complaints.edit');
        $complaints = Complaint::query()->when(! $canSeeAll, fn($q) => $q->where('raised_by', auth()->id()))->orderByDesc('created_at')->paginate(15);
        return view('livewire.tenant.complaints.index', ['complaints' => $complaints, 'complaintService' => $complaintService]);
    }
}
