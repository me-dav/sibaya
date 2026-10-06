<x-layouts.admin title="Tambah Siswa">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Tambah Siswa</h1>
    @include('admin.students._form', ['action' => route('admin.students.store'), 'method' => 'POST'])
</x-layouts.admin>
