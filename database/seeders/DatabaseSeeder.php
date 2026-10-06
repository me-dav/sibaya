<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RinenggaTypeSeeder::class,
            MaterialSeeder::class,  // dummy, hanya untuk development
            ExerciseSeeder::class,  // dummy, hanya untuk development
            // EvaluationSeeder ditambah di Tahap 8
        ]);
    }
}
