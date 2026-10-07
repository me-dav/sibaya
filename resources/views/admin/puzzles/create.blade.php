<x-layouts.admin title="Tambah Puzzle">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Tambah Puzzle</h1>
    @include('admin.puzzles._form', ['action' => route('admin.puzzles.store'), 'method' => 'POST'])
</x-layouts.admin>
