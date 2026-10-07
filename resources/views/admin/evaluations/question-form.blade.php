<x-layouts.admin :title="$title">
    <x-breadcrumb :items="['Evaluasi' => route('admin.evaluations.index'), $evaluation->title => route('admin.evaluations.show', $evaluation), $title => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">{{ $title }}</h1>
    @if ($isUsed)
        <div role="note" class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            Soal iki wis dijawab siswa. Owah-owahan kunci utawa bobot ora ngowahi nilai sing wis kasimpen.
        </div>
    @endif
    @include('admin.questions._form', ['exercise' => null])
</x-layouts.admin>
