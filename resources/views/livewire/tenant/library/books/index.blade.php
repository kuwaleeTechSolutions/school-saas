<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-slate-800">Library Catalogue</h1>
        @can('create', \App\Domain\Tenant\Models\LibraryBook::class)<a href="{{ route('tenant.library.books.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">+ Add Book</a>@endcan
    </div>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search title or author&hellip;" class="mb-4 w-full rounded-lg border-slate-300 text-sm">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-medium text-slate-600">Title</th><th class="px-4 py-3 text-left font-medium text-slate-600">Author</th><th class="px-4 py-3 text-left font-medium text-slate-600">Category</th><th class="px-4 py-3 text-left font-medium text-slate-600">Copies</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($books as $book)
                    <tr wire:key="book-{{ $book->id }}"><td class="px-4 py-3 font-medium text-slate-800">{{ $book->title }}</td><td class="px-4 py-3 text-slate-600">{{ $book->author ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ $book->category ?? '—' }}</td><td class="px-4 py-3 text-slate-600">{{ $book->available_count }} / {{ $book->copies_count }} available</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">No books in the catalogue yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $books->links() }}</div>
</div>
