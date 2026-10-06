<x-layouts.admin title="Tambah Soal">
    <x-breadcrumb :items="['Gladhen' => route('admin.exercises.index'), $exercise->title => route('admin.exercises.show', $exercise), 'Tambah Soal' => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">Tambah Soal</h1>
    @include('admin.questions._form', ['action' => route('admin.exercises.questions.store', $exercise), 'method' => 'POST'])
</x-layouts.admin>
