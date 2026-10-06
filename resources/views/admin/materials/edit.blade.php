<x-layouts.admin title="Ubah Materi">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Ubah Materi</h1>
    @include('admin.materials._form', [
        'action' => route('admin.materials.update', $material),
        'method' => 'PUT',
    ])
</x-layouts.admin>
