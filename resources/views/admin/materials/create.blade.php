<x-layouts.admin title="Tambah Materi">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Tambah Materi</h1>
    @include('admin.materials._form', [
        'material' => new \App\Models\Material(),
        'action'   => route('admin.materials.store'),
        'method'   => 'POST',
    ])
</x-layouts.admin>
