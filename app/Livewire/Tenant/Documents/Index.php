<?php
namespace App\Livewire\Tenant\Documents;
use App\Domain\Tenant\Models\SchoolDocument;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;
    public string $title = '';
    public string $category = '';
    public $file;
    public string $audienceType = 'school_wide';
    public string $audienceRole = '';

    public function mount(): void { $this->authorize('viewAny', SchoolDocument::class); }

    public function upload(): void
    {
        $this->authorize('create', SchoolDocument::class);
        $this->validate(['title' => ['required', 'string', 'max:255'], 'file' => ['required', 'file', 'max:10240'], 'audienceRole' => ['required_if:audienceType,role']]);
        $path = $this->file->store('school-documents', 'local');
        SchoolDocument::create(['title' => $this->title, 'category' => $this->category ?: null, 'file_path' => $path, 'original_name' => $this->file->getClientOriginalName(), 'audience_type' => $this->audienceType, 'audience_role' => $this->audienceType === 'role' ? $this->audienceRole : null, 'uploaded_by' => auth()->id()]);
        $this->reset(['title', 'category', 'file', 'audienceRole']);
        session()->flash('success', 'Document uploaded.');
    }

    public function render()
    {
        $roleNames = auth()->user()->roles()->pluck('name')->all();
        $documents = SchoolDocument::query()
            ->where(fn($q) => $q->where('audience_type', 'school_wide')->orWhere(fn($q2) => $q2->where('audience_type', 'role')->whereIn('audience_role', $roleNames)))
            ->orderByDesc('created_at')->get();
        return view('livewire.tenant.documents.index', ['documents' => $documents, 'roles' => Role::whereNull('school_id')->orderBy('name')->get()]);
    }
}
