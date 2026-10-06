<?php

namespace Database\Seeders;

use App\Models\RinenggaType;
use Illuminate\Database\Seeder;

class RinenggaTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Deskripsi singkat diambil dari gambar referensi desain. Admin dapat mengubahnya nanti.
        $types = [
            ['Paribasan', 'paribasan', 'Unen-unen kang wis gumathok panganggone lan nduweni teges tartamtu.'],
            ['Bebasan', 'bebasan', 'Unen-unen kang nduweni teges entar lan biasane nggambarke watak utawa kahanan manungsa.'],
            ['Saloka', 'saloka', 'Unen-unen kang nggunakake pralambang kanggo nggambarke kahanan utawa wong.'],
            ['Pepindhan', 'pepindhan', 'Mbandhingake siji bab karo bab liyane, biasane nggunakake tembung kaya, kadya, lir, pindha, umpama.'],
            ['Sanepa', 'sanepa', 'Unen-unen kang ora langsung lan nduweni teges kosok balen utawa ora salugune.'],
            ['Tembung Entar', 'tembung-entar', 'Gabungan tembung kang nduweni teges ora salugune, nanging nduweni teges kiasan.'],
        ];

        foreach ($types as $i => [$name, $slug, $short]) {
            RinenggaType::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'short_description' => $short,
                    'sort_order' => $i + 1,
                    'is_published' => true,
                ]
            );
        }
    }
}
