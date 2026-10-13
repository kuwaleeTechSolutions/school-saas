<?php
namespace App\Livewire\Tenant\Library\Books;
use App\Domain\Tenant\Models\LibraryBook;
use App\Domain\Tenant\Models\LibraryBookCopy;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public string $title = '';
    public string $author = '';
    public string $isbn = '';
    public string $category = '';
    public string $publisher = '';
    public int $copyCount = 1;

    public function mount(): void { $this->authorize('create', LibraryBook::class); }

    public function save(): void
    {
        $this->validate(['title' => ['required', 'string', 'max:255'], 'copyCount' => ['required', 'integer', 'min:1', 'max:100']]);
        $book = LibraryBook::create(['title' => $this->title, 'author' => $this->author ?: null, 'isbn' => $this->isbn ?: null, 'category' => $this->category ?: null, 'publisher' => $this->publisher ?: null]);
        for ($i = 1; $i <= $this->copyCount; $i++) {
            LibraryBookCopy::create(['library_book_id' => $book->id, 'accession_number' => strtoupper(substr(md5($book->id . '-' . $i . '-' . uniqid()), 0, 10)), 'status' => 'available']);
        }
        session()->flash('success', 'Book added with ' . $this->copyCount . ' cop' . ($this->copyCount === 1 ? 'y' : 'ies') . '.');
        $this->redirect(route('tenant.library.books.index'), navigate: true);
    }

    public function render() { return view('livewire.tenant.library.books.create'); }
}
