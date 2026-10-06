<x-layouts.admin title="Ubah Latihan">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Ubah Latihan</h1>
    @include('admin.exercises._form', ['action' => route('admin.exercises.update', $exercise), 'method' => 'PUT'])
</x-layouts.admin>
