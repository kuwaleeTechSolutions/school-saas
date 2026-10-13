<?php
namespace App\Livewire\Tenant\Library\Books;
use App\Domain\Tenant\Models\LibraryBook;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public function mount(): void { $this->authorize('viewAny', LibraryBook::class); }
    public function render()
    {
        $books = LibraryBook::query()->withCount(['copies', 'copies as available_count' => fn($q) => $q->where('status', 'available')])
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")->orWhere('author', 'like', "%{$this->search}%"))
            ->orderBy('title')->paginate(15);
        return view('livewire.tenant.library.books.index', ['books' => $books]);
    }
}
