<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $students = $this->students()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])->orWhereRaw('LOWER(email) LIKE ?', ["%{$q}%"])))
            ->when($request->filled('class'), fn ($query) => $query->where('class_name', $request->query('class')))
            ->when(in_array($request->query('status'), ['aktif', 'nonaktif'], true),
                fn ($query) => $query->where('is_active', $request->query('status') === 'aktif'))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classes'  => $this->students()->whereNotNull('class_name')->distinct()->orderBy('class_name')->pluck('class_name'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.students.create', ['student' => new User(['is_active' => true])]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $student = new User();
        $student->forceFill([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => UserRole::SISWA->value, // role tidak pernah diambil dari form
            'class_name'        => $data['class_name'],
            'school_name'       => $data['school_name'],
            'is_active'         => $data['is_active'],
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('admin.students.show', $student)->with('status', 'Siswa kasil ditambahake.');
    }

    public function show(User $student): View
    {
        $this->ensureStudent($student);
        Gate::authorize('view', $student);

        return view('admin.students.show', [
            'student'            => $student,
            'exerciseAttempts'   => $student->exerciseAttempts()->with('exercise')->latest('started_at')->limit(10)->get(),
            'exerciseTotal'      => DB::table('exercise_attempts')->where('user_id', $student->id)->count(),
            'evaluationAttempts' => $student->evaluationAttempts()->with('evaluation')->latest('started_at')->limit(10)->get(),
        ]);
    }

    public function edit(User $student): View
    {
        $this->ensureStudent($student);
        Gate::authorize('update', $student);

        return view('admin.students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $data = $request->validated();

        $student->forceFill([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'class_name'  => $data['class_name'],
            'school_name' => $data['school_name'],
            'is_active'   => $data['is_active'],
        ]);

        if (filled($data['password'] ?? null)) {
            $student->password = Hash::make($data['password']);
        }

        $student->save();

        return redirect()->route('admin.students.show', $student)->with('status', 'Data siswa kasil dianyari.');
    }

    public function toggle(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        Gate::authorize('update', $student);

        $student->forceFill(['is_active' => ! $student->is_active])->save();

        return back()->with('status', $student->is_active ? 'Akun siswa diaktifake.' : 'Akun siswa dinonaktifake.');
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        Gate::authorize('delete', $student);

        if ($this->hasHistory($student)) {
            return back()->with('error', 'Siswa iki wis duwe riwayat belajar, ora bisa dibusak. Nonaktifake wae akune.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('status', 'Siswa kasil dibusak.');
    }

    private function students()
    {
        return User::query()->where('role', UserRole::SISWA->value);
    }

    /** Route siswa tidak boleh dipakai untuk membuka/mengubah akun admin. */
    private function ensureStudent(User $user): void
    {
        abort_unless((($user->role instanceof UserRole) ? $user->role->value : $user->role) === UserRole::SISWA->value, 404);
    }

    private function hasHistory(User $student): bool
    {
        foreach (['exercise_attempts', 'evaluation_attempts', 'recommendations', 'certificates'] as $table) {
            if (DB::table($table)->where('user_id', $student->id)->exists()) {
                return true;
            }
        }

        return false;
    }
}
