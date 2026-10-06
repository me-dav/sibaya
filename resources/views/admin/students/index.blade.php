<x-layouts.admin title="Siswa">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-sibaya-navy-800">Siswa</h1>
        <a href="{{ route('admin.students.create') }}" class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Tambah Siswa</a>
    </div>
    <x-flash />

    <form method="GET" class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div><label for="q" class="block text-sm font-medium">Golek jeneng/email</label>
            <input id="q" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        <div><label for="class" class="block text-sm font-medium">Kelas</label>
            <select id="class" name="class" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Kabeh</option>
                @foreach ($classes as $c)<option value="{{ $c }}" @selected(request('class') === $c)>{{ $c }}</option>@endforeach
            </select></div>
        <div><label for="status" class="block text-sm font-medium">Status akun</label>
            <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Kabeh</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select></div>
        <div class="flex items-end"><button class="w-full rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Filter</button></div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr>
                <th scope="col" class="px-4 py-3 font-semibold">Jeneng</th><th scope="col" class="px-4 py-3 font-semibold">Email</th>
                <th scope="col" class="px-4 py-3 font-semibold">Kelas</th><th scope="col" class="px-4 py-3 font-semibold">Sekolah</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $s)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $s->name }}</td>
                        <td class="px-4 py-3">{{ $s->email }}</td>
                        <td class="px-4 py-3">{{ $s->class_name }}</td>
                        <td class="px-4 py-3">{{ $s->school_name }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $s->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.students.show', $s) }}" class="text-sibaya-navy-700 hover:underline">Detail</a>
                            <a href="{{ route('admin.students.edit', $s) }}" class="ml-3 text-sibaya-navy-700 hover:underline">Ubah</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Ora ana siswa sing cocog.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
</x-layouts.admin>
