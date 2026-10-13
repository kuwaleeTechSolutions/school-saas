<?php
namespace App\Policies;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\ExamSubject;
use App\Models\User;
class ExamSubjectPolicy {
    public function __construct(protected EntitlementServiceContract $entitlements) {}
    public function enterMarks(User $user, ExamSubject $examSubject): bool { return $this->entitlements->can('examinations.marks.enter'); }
    public function verify(User $user, ExamSubject $examSubject): bool { return $this->entitlements->can('examinations.marks.approve'); }
    public function publish(User $user, ExamSubject $examSubject): bool { return $this->entitlements->can('results.publish'); }
    public function correctAfterPublish(User $user, ExamSubject $examSubject): bool { return $this->entitlements->can('results.edit_published'); }
}
