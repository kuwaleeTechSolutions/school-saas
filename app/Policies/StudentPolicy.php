<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Student;
use App\Models\User;
class StudentPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function viewAny(User $user): bool { return $this->entitlements->can('students.view'); }
    public function view(User $user, Student $student): bool { return $this->entitlements->can('students.view') && $this->withinScope($user, $student); }
    public function create(User $user): bool { return $this->entitlements->can('students.create'); }
    public function update(User $user, Student $student): bool { return $this->entitlements->can('students.edit') && $this->withinScope($user, $student); }
    public function delete(User $user, Student $student): bool { return $this->entitlements->can('students.delete') && $this->withinScope($user, $student); }
    public function export(User $user): bool { return $this->entitlements->can('students.export'); }
    protected function withinScope(User $user, Student $student): bool
    {
        $scope = app(\App\Domain\Shared\Permission\Contracts\PermissionServiceContract::class)->effectiveScope($user, 'students.view');
        if ($scope !== 'assigned_classes_only') return true;
        $teacher = \App\Domain\Tenant\Models\Teacher::where('user_id', $user->id)->first();
        if (! $teacher) return false;
        $enrollment = $student->currentEnrollment;
        if (! $enrollment) return false;
        return $teacher->subjectAssignments()->where('class_id', $enrollment->class_id)
            ->where(fn($q) => $q->whereNull('section_id')->orWhere('section_id', $enrollment->section_id))->exists();
    }
}
