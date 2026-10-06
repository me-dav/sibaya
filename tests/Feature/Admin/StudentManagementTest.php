<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use MakesExercises, MakesMaterials, RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Siti', 'email' => 'siti@example.com', 'password' => 'Rahasia-12345', 'password_confirmation' => 'Rahasia-12345',
            'class_name' => 'X-1', 'school_name' => 'SMA 1', 'is_active' => 1,
        ], $over);
    }

    public function test_siswa_tidak_dapat_mengelola_siswa(): void
    {
        $this->actingAs($this->siswa())->get(route('admin.students.index'))->assertForbidden();
        $this->actingAs($this->siswa())->post(route('admin.students.store'), $this->payload())->assertForbidden();
    }

    public function test_admin_membuat_siswa_role_selalu_siswa_dan_sandi_di_hash(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->payload(['role' => 'admin']))
            ->assertRedirect();

        $u = User::where('email', 'siti@example.com')->firstOrFail();
        $this->assertSame('siswa', $u->role instanceof \BackedEnum ? $u->role->value : $u->role);
        $this->assertTrue(Hash::check('Rahasia-12345', $u->password));
    }

    public function test_validasi_kelas_dan_sekolah_wajib_serta_email_unik(): void
    {
        $this->siswa()->forceFill(['email' => 'siti@example.com'])->save();

        $this->actingAs($this->admin())
            ->post(route('admin.students.store'), $this->payload(['class_name' => '', 'school_name' => '']))
            ->assertSessionHasErrors(['class_name', 'school_name', 'email']);
    }

    public function test_ubah_tanpa_sandi_mempertahankan_sandi_lama(): void
    {
        $s = $this->siswa();
        $old = $s->password;

        $this->actingAs($this->admin())
            ->put(route('admin.students.update', $s), $this->payload(['email' => $s->email, 'password' => '', 'password_confirmation' => '', 'name' => 'Baru']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Baru', $s->fresh()->name);
        $this->assertSame($old, $s->fresh()->password);
    }

    public function test_toggle_aktif(): void
    {
        $s = $this->siswa();
        $this->actingAs($this->admin())->patch(route('admin.students.toggle', $s))->assertRedirect();
        $this->assertFalse((bool) $s->fresh()->is_active);
    }

    public function test_hapus_diblokir_jika_ada_riwayat_dan_boleh_jika_tidak(): void
    {
        $admin = $this->admin();
        $withHistory = $this->siswa();
        $clean = $this->siswa();
        $this->makeAttempt($withHistory->id, $this->makeExercise()->id);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $withHistory))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $withHistory->id]);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $clean))->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseMissing('users', ['id' => $clean->id]);
    }

    public function test_route_siswa_tidak_membuka_akun_admin(): void
    {
        $other = $this->admin();
        $this->actingAs($this->admin())->get(route('admin.students.edit', $other))->assertNotFound();
    }
}
