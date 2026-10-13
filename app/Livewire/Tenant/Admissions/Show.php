<?php
namespace App\Livewire\Tenant\Admissions;
use App\Domain\Tenant\Models\AdmissionApplication;
use App\Domain\Tenant\Services\AdmissionWorkflowService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public AdmissionApplication $application;
    public string $transitionNotes = '';
    public string $rejectionReason = '';

    public function mount(AdmissionApplication $application): void
    {
        $this->authorize('view', $application);
        $this->application = $application;
    }

    public function transitionTo(string $stage, AdmissionWorkflowService $workflow): void
    {
        $this->authorize('transition', $this->application);
        if ($stage === 'rejected' && trim($this->rejectionReason) === '') {
            $this->addError('rejectionReason', 'Please provide a reason for rejection.');
            return;
        }
        try {
            if ($stage === 'rejected') {
                $this->application->rejection_reason = $this->rejectionReason;
                $this->application->save();
            }
            $workflow->transition($this->application, $stage, auth()->user(), $this->transitionNotes ?: null);
            $this->application = $this->application->fresh();
            $this->transitionNotes = '';
            $this->rejectionReason = '';
            session()->flash('success', "Application moved to '{$stage}'.");
        } catch (RuntimeException $e) {
            $this->addError('transition', $e->getMessage());
        }
    }

    public function render(AdmissionWorkflowService $workflow)
    {
        $this->application->load(['stageHistories', 'documents', 'academicSession', 'student']);
        return view('livewire.tenant.admissions.show', ['availableNextStages' => $workflow->availableNextStages($this->application)]);
    }
}
