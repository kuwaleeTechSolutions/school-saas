<?php
namespace App\Livewire\Tenant\Library\Circulation;
use App\Domain\Tenant\Models\LibraryBookCopy;
use App\Domain\Tenant\Models\LibraryIssue;
use App\Domain\Tenant\Models\Student;
use App\Domain\Tenant\Services\LibraryService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ?int $copyId = null;
    public ?int $studentId = null;
    public string $dueOn;

    public function mount(): void { $this->authorize('create', LibraryIssue::class); $this->dueOn = now()->addDays(14)->format('Y-m-d'); }

    public function issue(LibraryService $libraryService): void
    {
        $this->validate(['copyId' => ['required', 'exists:library_book_copies,id'], 'studentId' => ['required', 'exists:students,id'], 'dueOn' => ['required', 'date', 'after:today']]);
        try {
            $copy = LibraryBookCopy::findOrFail($this->copyId);
            $student = Student::findOrFail($this->studentId);
            $libraryService->issueBook($copy, $student, Carbon::parse($this->dueOn), auth()->user());
            session()->flash('success', 'Book issued.');
            $this->reset(['copyId', 'studentId']);
        } catch (RuntimeException $e) {
            $this->addError('copyId', $e->getMessage());
        }
    }

    public function returnBook(int $issueId, LibraryService $libraryService): void
    {
        $this->authorize('update', LibraryIssue::class);
        try {
            $issue = LibraryIssue::findOrFail($issueId);
            $libraryService->returnBook($issue, auth()->user());
            session()->flash('success', 'Book returned.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function waiveFine(int $issueId, LibraryService $libraryService): void
    {
        $this->authorize('update', LibraryIssue::class);
        $issue = LibraryIssue::findOrFail($issueId);
        $libraryService->waiveFine($issue, auth()->user());
        session()->flash('success', 'Fine waived.');
    }

    public function render()
    {
        return view('livewire.tenant.library.circulation.index', [
            'availableCopies' => LibraryBookCopy::where('status', 'available')->with('book')->get(),
            'students' => Student::where('status', 'active')->orderBy('first_name')->get(),
            'currentlyOut' => LibraryIssue::whereNull('returned_on')->with(['copy.book', 'student'])->orderBy('due_on')->get(),
        ]);
    }
}
