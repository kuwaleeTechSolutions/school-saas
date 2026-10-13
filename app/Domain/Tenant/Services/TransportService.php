<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StudentTransportAssignment;
use App\Domain\Tenant\Models\TransportRoute;
use App\Models\User;
use RuntimeException;

class TransportService
{
    public function __construct(protected AuditService $audit) {}

    public function assignStudent(Student $student, TransportRoute $route, int $academicSessionId, ?int $stopId, User $actor): StudentTransportAssignment
    {
        if (! $route->is_active) {
            throw new RuntimeException('Cannot assign a student to an inactive route.');
        }
        $assignment = StudentTransportAssignment::updateOrCreate(
            ['student_id' => $student->id, 'academic_session_id' => $academicSessionId],
            ['transport_route_id' => $route->id, 'transport_stop_id' => $stopId]
        );
        $this->audit->logSchool($actor->id, 'transport.student_assigned', StudentTransportAssignment::class, $assignment->id, null, ['student_id' => $student->id, 'route_id' => $route->id]);
        return $assignment;
    }

    public function unassignStudent(Student $student, int $academicSessionId, User $actor): void
    {
        $assignment = StudentTransportAssignment::where('student_id', $student->id)->where('academic_session_id', $academicSessionId)->first();
        if ($assignment) {
            $assignment->delete();
            $this->audit->logSchool($actor->id, 'transport.student_unassigned', StudentTransportAssignment::class, $assignment->id, ['student_id' => $student->id]);
        }
    }
}
