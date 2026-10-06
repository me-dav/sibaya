<x-layouts.admin title="Ubah Soal">
    <x-breadcrumb :items="['Gladhen' => route('admin.exercises.index'), $exercise->title => route('admin.exercises.show', $exercise), 'Ubah Soal' => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">Ubah Soal</h1>
    @if ($isUsed)
        <div role="note" class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            Soal iki wis dijawab siswa utawa dipakai evaluasi. Owah-owahan kunci utawa bobot ora ngowahi nilai sing wis kasimpen.
        </div>
    @endif
    @include('admin.questions._form', ['action' => route('admin.questions.update', $question), 'method' => 'PUT', 'allowedTypes' => []])
</x-layouts.admin>
