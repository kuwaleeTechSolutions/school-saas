<?php
namespace App\Domain\Shared\Audit;
use App\Domain\Platform\Models\PlatformAuditLog;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\SchoolAuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function logPlatform(?int $adminId, string $action, ?string $subjectType = null, ?int $subjectId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        PlatformAuditLog::create(['admin_id'=>$adminId,'action'=>$action,'subject_type'=>$subjectType,'subject_id'=>$subjectId,'old_values'=>$oldValues,'new_values'=>$newValues,'ip_address'=>Request::ip(),'user_agent'=>substr((string)Request::header('User-Agent'),0,255),'created_at'=>now()]);
    }
    public function logSchool(int $userId, string $action, ?string $subjectType = null, ?int $subjectId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $schoolId = app(TenantContext::class)->currentSchoolId();
        if (! $schoolId) return;
        SchoolAuditLog::create(['school_id'=>$schoolId,'user_id'=>$userId,'action'=>$action,'subject_type'=>$subjectType,'subject_id'=>$subjectId,'old_values'=>$oldValues,'new_values'=>$newValues,'ip_address'=>Request::ip(),'user_agent'=>substr((string)Request::header('User-Agent'),0,255),'created_at'=>now()]);
    }
}
