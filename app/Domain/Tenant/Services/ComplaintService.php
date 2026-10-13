<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Complaint;
use App\Models\User;
use RuntimeException;

class ComplaintService
{
    protected const TRANSITIONS = [
        'open' => ['in_progress'], 'in_progress' => ['resolved'], 'resolved' => ['closed', 'in_progress'],
    ];

    public function __construct(protected AuditService $audit) {}

    public function availableNextStatuses(Complaint $complaint): array { return self::TRANSITIONS[$complaint->status] ?? []; }

    public function assign(Complaint $complaint, User $assignee, User $actor): Complaint
    {
        $complaint->update(['assigned_to' => $assignee->id]);
        $this->audit->logSchool($actor->id, 'complaint.assigned', Complaint::class, $complaint->id, null, ['assigned_to' => $assignee->id]);
        return $complaint->fresh();
    }

    public function transition(Complaint $complaint, string $toStatus, User $actor, ?string $resolutionNotes = null): Complaint
    {
        if (! in_array($toStatus, $this->availableNextStatuses($complaint), true)) {
            throw new RuntimeException("Cannot move complaint from '{$complaint->status}' to '{$toStatus}'.");
        }
        $fromStatus = $complaint->status;
        $updates = ['status' => $toStatus];
        if ($toStatus === 'resolved') {
            $updates['resolution_notes'] = $resolutionNotes;
            $updates['resolved_at'] = now();
        }
        $complaint->update($updates);
        $this->audit->logSchool($actor->id, 'complaint.status_changed', Complaint::class, $complaint->id, ['status' => $fromStatus], ['status' => $toStatus]);
        return $complaint->fresh();
    }
}
