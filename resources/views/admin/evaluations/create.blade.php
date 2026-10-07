<x-layouts.admin title="Tambah Evaluasi">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Tambah Evaluasi</h1>
    @include('admin.evaluations._form', ['action' => route('admin.evaluations.store'), 'method' => 'POST'])
</x-layouts.admin>
