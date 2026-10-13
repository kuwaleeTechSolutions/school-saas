<?php
namespace App\Domain\Tenant\Services;
use App\Domain\Shared\Audit\AuditService;
use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\AdmissionApplication;
use App\Domain\Tenant\Models\AdmissionStageHistory;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdmissionWorkflowService
{
    protected const TRANSITIONS = [
        'enquiry' => ['application'], 'application' => ['submitted'], 'submitted' => ['under_review'],
        'under_review' => ['document_verification'], 'document_verification' => ['entrance_test','interview','selected'],
        'entrance_test' => ['interview','selected'], 'interview' => ['selected','waitlisted'], 'waitlisted' => ['selected'],
        'selected' => ['fee_pending'], 'fee_pending' => ['admitted'],
    ];
    protected const TERMINAL_FROM_ANY = ['rejected','cancelled'];
    public function __construct(protected AuditService $audit, protected EntitlementServiceContract $entitlements) {}
    public function availableNextStages(AdmissionApplication $application): array
    {
        if (in_array($application->stage, ['admitted','rejected','cancelled'], true)) return [];
        return array_merge(self::TRANSITIONS[$application->stage] ?? [], self::TERMINAL_FROM_ANY);
    }
    public function canTransition(AdmissionApplication $application, string $toStage): bool { return in_array($toStage, $this->availableNextStages($application), true); }
    public function transition(AdmissionApplication $application, string $toStage, User $actor, ?string $notes = null): AdmissionApplication
    {
        if (! $this->canTransition($application, $toStage)) throw new RuntimeException("Cannot move application from '{$application->stage}' to '{$toStage}'.");
        return DB::transaction(function () use ($application, $toStage, $actor, $notes) {
            $fromStage = $application->stage;
            AdmissionStageHistory::create(['school_id'=>$application->school_id,'admission_application_id'=>$application->id,'from_stage'=>$fromStage,'to_stage'=>$toStage,'changed_by'=>$actor->id,'notes'=>$notes,'created_at'=>now()]);
            $application->stage = $toStage;
            if ($toStage === 'admitted') $this->admitApplication($application);
            $application->save();
            $this->audit->logSchool($actor->id, 'admission.stage_changed', AdmissionApplication::class, $application->id, ['stage'=>$fromStage], ['stage'=>$toStage]);
            return $application->fresh();
        });
    }
    protected function admitApplication(AdmissionApplication $application): void
    {
        if ($application->student_id) return;
        if (! $this->entitlements->limitAvailable('students', 1)) throw new RuntimeException("Admitting this applicant would exceed your plan's student limit.");
        $section = Section::whereHas('schoolClass', function ($q) use ($application) { $q->where('academic_session_id', $application->academic_session_id)->where('name', $application->applying_for_class); })->first();
        if (! $section) throw new RuntimeException("No class/section named '{$application->applying_for_class}' exists for this session yet. Create it first.");
        $student = Student::create(['admission_number'=>$this->generateAdmissionNumber($application),'first_name'=>$application->applicant_first_name,'last_name'=>$application->applicant_last_name,'gender'=>$application->applicant_gender,'date_of_birth'=>$application->applicant_date_of_birth,'previous_school'=>$application->previous_school,'admission_date'=>now(),'status'=>'active']);
        $student->enrollments()->create(['academic_session_id'=>$application->academic_session_id,'class_id'=>$section->class_id,'section_id'=>$section->id,'status'=>'active']);
        $application->student_id = $student->id;
    }
    protected function generateAdmissionNumber(AdmissionApplication $application): string
    {
        $year = now()->format('y'); $sequence = Student::where('school_id', $application->school_id)->count() + 1;
        return "ADM{$year}-" . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
