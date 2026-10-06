<x-layouts.admin title="Tambah Latihan">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Tambah Latihan</h1>
    @include('admin.exercises._form', ['action' => route('admin.exercises.store'), 'method' => 'POST'])
</x-layouts.admin>
