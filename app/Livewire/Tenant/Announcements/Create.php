<?php

namespace App\Livewire\Tenant\Announcements;

use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenant\Models\Announcement;
use App\Domain\Tenant\Models\SchoolClass;
use App\Domain\Tenant\Models\Section;
use App\Domain\Tenant\Services\AnnouncementService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $title = '';
    public string $body = '';
    public string $audienceType = 'school_wide';
    public ?int $classId = null;
    public ?int $sectionId = null;
    public string $role = '';
    public bool $alsoEmail = false;

    public function mount(): void
    {
        $this->authorize('create', Announcement::class);
    }

    public function save(AnnouncementService $announcementService): void
    {
        $this->authorize('create', Announcement::class);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'audienceType' => ['required', 'in:school_wide,class,section,role'],
            'classId' => ['required_if:audienceType,class', 'nullable', 'exists:classes,id'],
            'sectionId' => ['required_if:audienceType,section', 'nullable', 'exists:sections,id'],
            'role' => ['required_if:audienceType,role', 'nullable', 'string'],
        ]);

        $audienceFilters = match ($this->audienceType) {
            'class' => ['class_id' => $this->classId],
            'section' => ['section_id' => $this->sectionId],
            'role' => ['role' => $this->role],
            default => null,
        };

        $announcementService->publish([
            'title' => $this->title,
            'body' => $this->body,
            'audience_type' => $this->audienceType,
            'audience_filters' => $audienceFilters,
        ], auth()->user(), $this->alsoEmail);

        session()->flash('success', 'Announcement published.');
        $this->redirect(route('tenant.announcements.index'), navigate: true);
    }

    public function render()
    {
        $school = app(TenantContext::class)->school();
        $session = $school->activeAcademicSession();

        return view('livewire.tenant.announcements.create', [
            'classes' => $session ? SchoolClass::where('academic_session_id', $session->id)->orderBy('sort_order')->get() : collect(),
            'sections' => $session ? Section::whereHas('schoolClass', fn ($q) => $q->where('academic_session_id', $session->id))->with('schoolClass')->get() : collect(),
            'roles' => Role::whereNull('school_id')->orWhere('school_id', $school->id)->orderBy('name')->get(),
        ]);
    }
}
