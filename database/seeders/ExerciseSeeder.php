<?php

namespace Database\Seeders;

use App\Enums\ExerciseType;
use App\Enums\QuestionType;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\RinenggaType;
use Illuminate\Database\Seeder;

/**
 * DATA DUMMY untuk development. Soal sebenarnya dibuat admin (Tahap 7).
 * Soal audio tidak diseed karena butuh file; uji lewat QuestionService.
 */
class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $paribasan = RinenggaType::where('slug', 'paribasan')->first();

        $this->mc('dummy-pemahaman-1', '[DUMMY] Pemahaman Basa Rinengga', ExerciseType::PEMAHAMAN, null, 5);
        $this->mc('dummy-paribasan', '[DUMMY] Latihan Paribasan', ExerciseType::MENURUT_MATERI, $paribasan?->id, 4);
        $this->mc('dummy-campuran', '[DUMMY] Latihan Campuran (diacak)', ExerciseType::CAMPURAN, null, 6, randomized: true);
        $this->isianDanAnalisis();
    }

    private function exercise(string $slug, string $title, ExerciseType $type, ?int $rinenggaTypeId, bool $randomized = false): Exercise
    {
        return Exercise::updateOrCreate(['slug' => $slug], [
            'title' => $title,
            'description' => 'Data dummy untuk pengembangan.',
            'exercise_type' => $type,
            'rinengga_type_id' => $rinenggaTypeId,
            'instructions' => 'Wangsulana kanthi tepat.',
            'is_randomized' => $randomized,
            'is_published' => true,
        ]);
    }

    private function mc(string $slug, string $title, ExerciseType $type, ?int $rinenggaTypeId, int $count, bool $randomized = false): void
    {
        $exercise = $this->exercise($slug, $title, $type, $rinenggaTypeId, $randomized);

        if ($exercise->questions()->exists()) {
            return;
        }

        $keys = ['A', 'B', 'C', 'D'];

        for ($i = 1; $i <= $count; $i++) {
            $question = Question::create([
                'exercise_id' => $exercise->id,
                'question_type' => QuestionType::MULTIPLE_CHOICE,
                'question_text' => "[DUMMY] Soal nomor {$i}",
                'answer_key' => $keys[($i - 1) % 4],
                'points' => 1,
                'sort_order' => $i,
            ]);

            foreach ($keys as $n => $key) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_key' => $key,
                    'option_text' => "[DUMMY] Pilihan {$key}",
                    'is_correct' => $key === $keys[($i - 1) % 4],
                    'sort_order' => $n,
                ]);
            }
        }
    }

    private function isianDanAnalisis(): void
    {
        $exercise = $this->exercise('dummy-isian-analisis', '[DUMMY] Isian dan Analisis', ExerciseType::ANALISIS, null);

        if ($exercise->questions()->exists()) {
            return;
        }

        Question::create([
            'exercise_id' => $exercise->id,
            'question_type' => QuestionType::SHORT_ANSWER,
            'question_text' => '[DUMMY] Tulis kata "dummy" atau "contoh".',
            'answer_key' => 'dummy',
            'keywords' => ['dummy', 'contoh'],
            'metadata' => ['match' => 'contains'],
            'points' => 2,
            'sort_order' => 1,
        ]);

        Question::create([
            'exercise_id' => $exercise->id,
            'question_type' => QuestionType::TEXT_ANALYSIS,
            'question_text' => '[DUMMY] Temokake jinis ing teks.',
            'passage_text' => '[DUMMY] Teks dummy: alpha beta gamma.',
            'metadata' => ['analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => ['alpha'], 'points' => 2],
                ['label' => 'Bebasan', 'keywords' => ['beta'], 'points' => 2],
                ['label' => 'Paribasan', 'keywords' => ['gamma'], 'points' => 1],
            ]],
            'points' => 5,
            'sort_order' => 2,
        ]);
    }
}
