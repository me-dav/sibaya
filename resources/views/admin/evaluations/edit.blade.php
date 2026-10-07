<x-layouts.admin title="Ubah Evaluasi">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Ubah Evaluasi</h1>
    @include('admin.evaluations._form', ['action' => route('admin.evaluations.update', $evaluation), 'method' => 'PUT'])
</x-layouts.admin>
