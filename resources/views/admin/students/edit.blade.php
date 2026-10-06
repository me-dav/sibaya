<x-layouts.admin title="Ubah Siswa">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Ubah Siswa</h1>
    @include('admin.students._form', ['action' => route('admin.students.update', $student), 'method' => 'PUT'])
</x-layouts.admin>
