<?php
namespace App\Livewire\Tenant\Certificates;
use App\Domain\Tenant\Models\CertificateTemplate;
use App\Domain\Tenant\Models\IssuedCertificate;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Services\CertificateService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Issue extends Component
{
    public ?int $templateId = null;
    public ?int $studentId = null;

    public function mount(): void { $this->authorize('create', IssuedCertificate::class); }

    public function issue(CertificateService $certificateService): void
    {
        $this->authorize('create', IssuedCertificate::class);
        $this->validate(['templateId' => ['required', 'exists:certificate_templates,id'], 'studentId' => ['required', 'exists:students,id']]);
        $template = CertificateTemplate::findOrFail($this->templateId);
        $student = Student::findOrFail($this->studentId);
        $certificate = $certificateService->issue($template, $student, auth()->user());
        session()->flash('success', 'Certificate issued: ' . $certificate->certificate_number);
        $this->reset(['templateId', 'studentId']);
    }

    public function render()
    {
        return view('livewire.tenant.certificates.issue', [
            'templates' => CertificateTemplate::where('is_active', true)->orderBy('name')->get(),
            'students' => Student::where('status', 'active')->orderBy('first_name')->get(),
            'recentlyIssued' => IssuedCertificate::with(['student', 'template'])->orderByDesc('created_at')->limit(10)->get(),
        ]);
    }
}
