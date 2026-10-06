<?php

namespace Tests\Support;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\RinenggaType;
use App\Models\User;

trait MakesMaterials
{
    protected function makeType(string $slug = 'paribasan', string $name = 'Paribasan', bool $published = true): RinenggaType
    {
        $type = new RinenggaType();
        $type->forceFill(['name' => $name, 'slug' => $slug, 'short_description' => 'Cekak', 'sort_order' => 1, 'is_published' => $published])->save();

        return $type;
    }

    protected function makeMaterial(RinenggaType $type, array $attrs = []): Material
    {
        $m = new Material();
        $m->forceFill(array_merge([
            'rinengga_type_id' => $type->id,
            'title'            => 'Materi '.$type->name,
            'slug'             => 'materi-'.$type->slug,
            'description'      => 'Deskripsi',
            'content'          => "## Isi\n\nTeks materi.",
            'is_published'     => true,
        ], $attrs))->save();

        return $m;
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN->value]);
    }

    protected function siswa(): User
    {
        return User::factory()->create(['role' => UserRole::SISWA->value]);
    }
}
