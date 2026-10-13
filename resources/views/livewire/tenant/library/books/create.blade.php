<div class="max-w-xl">
    <h1 class="text-lg font-semibold text-slate-800 mb-6">Add Book</h1>
    <form wire:submit="save" class="space-y-4">
        <div><label class="block text-sm font-medium text-slate-700">Title *</label><input wire:model="title" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm">@error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700">Author</label><input wire:model="author" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">ISBN</label><input wire:model="isbn" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Category</label><input wire:model="category" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700">Publisher</label><input wire:model="publisher" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700">Number of Copies *</label><input wire:model="copyCount" type="number" min="1" class="mt-1 w-32 rounded-lg border-slate-300 text-sm">@error('copyCount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror</div>
        <div class="flex justify-end gap-3 pt-2 border-t border-slate-100"><a href="{{ route('tenant.library.books.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</a><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Save Book</button></div>
    </form>
</div>
