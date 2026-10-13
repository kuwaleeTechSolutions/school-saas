<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\VisitorLog;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class VisitorService
{
    public function __construct(protected AuditService $audit) {}

    public function checkIn(string $name, ?string $phone, string $purpose, ?string $whomToMeet, User $actor): VisitorLog
    {
        $log = VisitorLog::create([
            'visitor_name' => $name, 'visitor_phone' => $phone, 'purpose' => $purpose, 'whom_to_meet' => $whomToMeet,
            'gate_pass_number' => 'GP-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
            'checked_in_at' => now(), 'logged_by' => $actor->id,
        ]);
        $this->audit->logSchool($actor->id, 'visitor.checked_in', VisitorLog::class, $log->id);
        return $log;
    }

    public function checkOut(VisitorLog $log, User $actor): VisitorLog
    {
        if ($log->isCheckedOut()) throw new RuntimeException('This visitor has already been checked out.');
        $log->update(['checked_out_at' => now()]);
        $this->audit->logSchool($actor->id, 'visitor.checked_out', VisitorLog::class, $log->id);
        return $log->fresh();
    }
}
