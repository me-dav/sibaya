<x-layouts.admin title="Ubah Puzzle">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Ubah Puzzle</h1>
    @include('admin.puzzles._form', ['action' => route('admin.puzzles.update', $puzzle), 'method' => 'PUT'])
</x-layouts.admin>
