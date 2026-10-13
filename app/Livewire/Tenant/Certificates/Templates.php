<?php
namespace App\Livewire\Tenant\Certificates;
use App\Domain\Tenant\Models\CertificateTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Templates extends Component
{
    public string $name = '';
    public string $type = 'bonafide';
    public string $bodyTemplate = "This is to certify that {{student_name}}, bearing admission number {{admission_number}}, is a bonafide student of {{class_section}} in this institution.";

    public function mount(): void { $this->authorize('viewAny', CertificateTemplate::class); }

    public function save(): void
    {
        $this->authorize('create', CertificateTemplate::class);
        $this->validate(['name' => ['required', 'string', 'max:255'], 'bodyTemplate' => ['required', 'string']]);
        CertificateTemplate::create(['name' => $this->name, 'type' => $this->type, 'body_template' => $this->bodyTemplate, 'is_active' => true]);
        $this->reset(['name']);
        session()->flash('success', 'Template created.');
    }

    public function render() { return view('livewire.tenant.certificates.templates', ['templates' => CertificateTemplate::orderBy('name')->get()]); }
}
