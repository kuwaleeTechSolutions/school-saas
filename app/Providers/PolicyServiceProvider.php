<?php

namespace App\Providers;

use App\Domain\Tenant\Models\AcademicSession;
use App\Domain\Tenant\Models\AdmissionApplication;
use App\Domain\Tenant\Models\Announcement;
use App\Domain\Tenant\Models\Assignment;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\CertificateTemplate;
use App\Domain\Tenant\Models\Complaint;
use App\Domain\Tenant\Models\Exam;
use App\Domain\Tenant\Models\ExamSubject;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\FeePayment;
use App\Domain\Tenant\Models\FeeRefund;
use App\Domain\Tenant\Models\FeeStructure;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\Homework;
use App\Domain\Tenant\Models\InventoryItem;
use App\Domain\Tenant\Models\IssuedCertificate;
use App\Domain\Tenant\Models\LeaveRequest;
use App\Domain\Tenant\Models\LibraryBook;
use App\Domain\Tenant\Models\LibraryIssue;
use App\Domain\Tenant\Models\Notification;
use App\Domain\Tenant\Models\PayrollRun;
use App\Domain\Tenant\Models\ReportCard;
use App\Domain\Tenant\Models\School;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\SchoolDocument;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Models\StaffProfile;
use App\Domain\Tenant\Models\Subject;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Models\Timetable;
use App\Domain\Tenant\Models\TransportRoute;
use App\Domain\Tenant\Models\VisitorLog;
use App\Models\User;
use App\Policies\AcademicSessionPolicy;
use App\Policies\AdmissionApplicationPolicy;
use App\Policies\AnnouncementPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\CertificateTemplatePolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\ExamPolicy;
use App\Policies\ExamSubjectPolicy;
use App\Policies\FeeInvoicePolicy;
use App\Policies\FeePaymentPolicy;
use App\Policies\FeeRefundPolicy;
use App\Policies\FeeStructurePolicy;
use App\Policies\GuardianPolicy;
use App\Policies\HomeworkPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\IssuedCertificatePolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\LibraryBookPolicy;
use App\Policies\LibraryIssuePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PayrollRunPolicy;
use App\Policies\ReportCardPolicy;
use App\Policies\RolePolicy;
use App\Policies\SchoolClassPolicy;
use App\Policies\SchoolDocumentPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StaffProfilePolicy;
use App\Policies\StudentPolicy;
use App\Policies\SubjectPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\TimetablePolicy;
use App\Policies\TransportRoutePolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitorLogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

/**
 * STAGE 7 OVERLAY NOTE: replaces the PolicyServiceProvider from Stages
 * 2-6 wholesale, adding only the 12 new Stage 7 registrations at the
 * bottom. Every prior registration is unchanged.
 */
class PolicyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);
        Gate::policy(Guardian::class, GuardianPolicy::class);
        Gate::policy(SchoolClass::class, SchoolClassPolicy::class);
        Gate::policy(Subject::class, SubjectPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        // Stage 3
        Gate::policy(AdmissionApplication::class, AdmissionApplicationPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendanceRecordPolicy::class);
        Gate::policy(AcademicSession::class, AcademicSessionPolicy::class);

        // Stage 4
        Gate::policy(Timetable::class, TimetablePolicy::class);
        Gate::policy(Homework::class, HomeworkPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(ExamSubject::class, ExamSubjectPolicy::class);
        Gate::policy(ReportCard::class, ReportCardPolicy::class);

        // Stage 5
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(FeeInvoice::class, FeeInvoicePolicy::class);
        Gate::policy(FeePayment::class, FeePaymentPolicy::class);
        Gate::policy(FeeRefund::class, FeeRefundPolicy::class);

        // Stage 6
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);

        // Stage 7
        Gate::policy(LibraryBook::class, LibraryBookPolicy::class);
        Gate::policy(LibraryIssue::class, LibraryIssuePolicy::class);
        Gate::policy(TransportRoute::class, TransportRoutePolicy::class);
        Gate::policy(InventoryItem::class, InventoryItemPolicy::class);
        Gate::policy(StaffProfile::class, StaffProfilePolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(PayrollRun::class, PayrollRunPolicy::class);
        Gate::policy(VisitorLog::class, VisitorLogPolicy::class);
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(SchoolDocument::class, SchoolDocumentPolicy::class);
        Gate::policy(CertificateTemplate::class, CertificateTemplatePolicy::class);
        Gate::policy(IssuedCertificate::class, IssuedCertificatePolicy::class);
    }
}
