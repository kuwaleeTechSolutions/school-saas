<?php

namespace App\Livewire\Tenant\ParentPortal;

use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\AttendanceRecord;
use App\Domain\Tenant\Models\FeeInvoice;
use App\Domain\Tenant\Models\Guardian;
use App\Domain\Tenant\Models\HomeworkSubmission;
use App\Domain\Tenant\Models\ReportCard;
use App\Domain\Tenant\Services\AnnouncementService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Section G.4 — "own_children_only" scope, enforced structurally rather
 * than by a runtime check: every query below starts from the Guardian row
 * tied to auth()->user()->id and only ever touches that guardian's OWN
 * linked students. There is no code path here that can be pointed at an
 * arbitrary student_id — the scope is the shape of the query itself, the
 * same pattern StudentPolicy::withinScope() uses for teachers
 * (Section G.4, Stage 2).
 */
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public ?int $guardianId = null;

    public function mount(EntitlementServiceContract $entitlements): void
    {
        abort_unless($entitlements->can('parent_portal.view'), 403, 'The Parent Portal is not included in your school\'s current plan.');

        $guardian = Guardian::where('user_id', auth()->id())->first();
        $this->guardianId = $guardian?->id;

        abort_unless($guardian, 403, 'No guardian profile is linked to your account.');
    }

    public function render(AnnouncementService $announcementService)
    {
        $guardian = Guardian::with(['students.currentEnrollment.schoolClass', 'students.currentEnrollment.section'])
            ->findOrFail($this->guardianId);

        $children = $guardian->students->map(function ($student) {
            $enrollment = $student->currentEnrollment;

            $attendanceTotal = AttendanceRecord::where('student_id', $student->id)
                ->whereHas('session', fn ($q) => $q->where('attendance_date', '>=', now()->subDays(30)))
                ->count();
            $attendancePresent = AttendanceRecord::where('student_id', $student->id)
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->whereHas('session', fn ($q) => $q->where('attendance_date', '>=', now()->subDays(30)))
                ->count();

            $pendingHomework = HomeworkSubmission::where('student_id', $student->id)
                ->where('status', 'pending')
                ->whereHas('homework', fn ($q) => $q->where('due_date', '>=', now()))
                ->count();

            $outstandingFees = FeeInvoice::where('student_id', $student->id)
                ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
                ->sum('balance_amount');

            $latestReportCard = ReportCard::where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->first();

            return [
                'student' => $student,
                'class_section' => $enrollment ? "{$enrollment->schoolClass->name} - {$enrollment->section->name}" : 'Not enrolled',
                'attendance_percent' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : null,
                'pending_homework' => $pendingHomework,
                'outstanding_fees' => (float) $outstandingFees,
                'latest_report_card' => $latestReportCard,
            ];
        });

        $announcements = $announcementService->applicableToQuery(auth()->user())
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('livewire.tenant.parent-portal.dashboard', ['children' => $children, 'announcements' => $announcements]);
    }
}
