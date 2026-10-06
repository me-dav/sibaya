<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\RinenggaType;
use Illuminate\Database\Seeder;

/**
 * DATA DUMMY untuk development. Isi materi sengaja placeholder;
 * isi sebenarnya ditulis admin lewat CRUD materi (Tahap 4).
 */
class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RinenggaType::ordered()->get() as $type) {
            Material::updateOrCreate(
                ['slug' => $type->slug],
                [
                    'rinengga_type_id' => $type->id,
                    'title' => $type->name,
                    'description' => $type->short_description ?? $type->name,
                    'content' => "[DUMMY] Isi materi {$type->name}. Ganti lewat dashboard admin.",
                    'examples' => "[DUMMY] Tuladha {$type->name}.",
                    'summary' => "[DUMMY] Rangkuman {$type->name}.",
                    'is_published' => true,
                ]
            );
        }
    }
}
