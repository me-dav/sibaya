<?php

namespace Tests\Feature\Admin;

use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class MaterialManagementTest extends TestCase
{
    use MakesMaterials, RefreshDatabase;

    private function payload(int $typeId, array $over = []): array
    {
        return array_merge([
            'rinengga_type_id' => $typeId,
            'title'            => 'Paribasan',
            'description'      => 'Deskripsi',
            'content'          => 'Isi materi',
            'is_published'     => 1,
        ], $over);
    }

    public function test_siswa_dan_tamu_tidak_dapat_mengakses_admin_materi(): void
    {
        $type = $this->makeType();
        $material = $this->makeMaterial($type);

        $this->get(route('admin.materials.index'))->assertRedirect(route('login'));
        $this->actingAs($this->siswa())->get(route('admin.materials.index'))->assertForbidden();
        $this->actingAs($this->siswa())->post(route('admin.materials.store'), $this->payload($type->id))->assertForbidden();
        $this->actingAs($this->siswa())->put(route('admin.materials.update', $material), $this->payload($type->id))->assertForbidden();
        $this->actingAs($this->siswa())->delete(route('admin.materials.destroy', $material))->assertForbidden();
    }

    public function test_admin_dapat_membuat_materi_dengan_slug_otomatis(): void
    {
        $type = $this->makeType();

        $this->actingAs($this->admin())
            ->post(route('admin.materials.store'), $this->payload($type->id, ['title' => 'Paribasan Jawa']))
            ->assertRedirect(route('admin.materials.index'));

        $this->assertDatabaseHas('materials', ['slug' => 'paribasan-jawa', 'rinengga_type_id' => $type->id]);
    }

    public function test_slug_harus_unik(): void
    {
        $a = $this->makeType('paribasan', 'Paribasan');
        $b = $this->makeType('bebasan', 'Bebasan');
        $this->makeMaterial($a, ['slug' => 'sama']);

        $this->actingAs($this->admin())
            ->post(route('admin.materials.store'), $this->payload($b->id, ['slug' => 'sama']))
            ->assertSessionHasErrors('slug');
    }

    public function test_satu_jenis_hanya_boleh_punya_satu_materi(): void
    {
        $type = $this->makeType();
        $this->makeMaterial($type);

        $this->actingAs($this->admin())
            ->post(route('admin.materials.store'), $this->payload($type->id, ['title' => 'Lain']))
            ->assertSessionHasErrors('rinengga_type_id');
    }

    public function test_upload_media_valid_tersimpan_dan_file_invalid_ditolak(): void
    {
        Storage::fake('public');
        $type = $this->makeType();

        $this->actingAs($this->admin())->post(route('admin.materials.store'), $this->payload($type->id, [
            'image' => UploadedFile::fake()->image('a.jpg'),
            'audio' => UploadedFile::fake()->create('a.mp3', 100, 'audio/mpeg'),
        ]))->assertSessionHasNoErrors();

        $m = Material::firstOrFail();
        Storage::disk('public')->assertExists([$m->image_path, $m->audio_path]);

        $other = $this->makeType('bebasan', 'Bebasan');
        $this->actingAs($this->admin())->post(route('admin.materials.store'), $this->payload($other->id, [
            'audio' => UploadedFile::fake()->create('virus.exe', 10),
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors(['audio', 'image']);
    }

    public function test_mengganti_media_menghapus_file_lama(): void
    {
        Storage::fake('public');
        $type = $this->makeType();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.materials.store'), $this->payload($type->id, ['image' => UploadedFile::fake()->image('a.jpg')]));
        $m = Material::firstOrFail();
        $oldPath = $m->image_path;

        $this->actingAs($admin)->put(route('admin.materials.update', $m), $this->payload($type->id, ['slug' => $m->slug, 'image' => UploadedFile::fake()->image('b.png')]))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($m->fresh()->image_path);
    }

    public function test_admin_dapat_mengubah_status_dan_menghapus_materi(): void
    {
        $type = $this->makeType();
        $m = $this->makeMaterial($type);
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.materials.update', $m), $this->payload($type->id, ['slug' => $m->slug, 'is_published' => 0]))->assertRedirect();
        $this->assertFalse($m->fresh()->is_published);

        $this->actingAs($admin)->delete(route('admin.materials.destroy', $m))->assertRedirect(route('admin.materials.index'));
        $this->assertDatabaseMissing('materials', ['id' => $m->id]);
    }
}
