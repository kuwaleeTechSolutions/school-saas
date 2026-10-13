<?php

namespace App\Livewire\Tenant\TeacherPortal;

use App\Domain\Shared\Entitlement\Contracts\EntitlementServiceContract;
use App\Domain\Tenant\Models\Homework;
use App\Domain\Tenant\Models\Teacher;
use App\Domain\Tenant\Models\TeacherSubjectAssignment;
use App\Domain\Tenant\Models\TimetableEntry;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Section G.4 — "assigned_classes_only" scope, enforced the same
 * structural way as the Parent Portal: every query starts from the
 * Teacher row tied to auth()->user()->id and only ever touches classes/
 * sections that teacher is actually assigned to via
 * teacher_subject_assignments — never an arbitrary class_id.
 */
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public ?int $teacherId = null;

    public function mount(EntitlementServiceContract $entitlements): void
    {
        abort_unless($entitlements->can('teacher_portal.view'), 403, 'The Teacher Portal is not included in your school\'s current plan.');

        $teacher = Teacher::where('user_id', auth()->id())->first();
        $this->teacherId = $teacher?->id;

        abort_unless($teacher, 403, 'No teacher profile is linked to your account.');
    }

    public function render()
    {
        $teacher = Teacher::with(['classesAsClassTeacher.schoolClass'])->findOrFail($this->teacherId);

        $assignments = TeacherSubjectAssignment::where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->get()
            ->unique(fn ($a) => $a->class_id . '-' . $a->section_id);

        $todayName = strtolower(Carbon::now()->format('l'));
        $todaysTimetable = TimetableEntry::where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayName)
            ->with(['period', 'subject', 'timetable.schoolClass', 'timetable.section'])
            ->get()
            ->sortBy(fn ($e) => $e->period->sort_order);

        $pendingGrading = Homework::where('teacher_id', $teacher->id)
            ->withCount(['submissions as ungraded_count' => fn ($q) => $q->whereIn('status', ['submitted', 'late'])])
            ->having('ungraded_count', '>', 0)
            ->get();

        return view('livewire.tenant.teacher-portal.dashboard', [
            'assignments' => $assignments,
            'todaysTimetable' => $todaysTimetable,
            'pendingGrading' => $pendingGrading,
            'classTeacherOf' => $teacher->classesAsClassTeacher,
        ]);
    }
}
