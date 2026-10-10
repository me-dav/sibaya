<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\Labels;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class QuestionService
{
    /** Audio soal disimpan di disk private (tidak punya URL publik). */
    public function disk(): string
    {
        return config('sibaya.private_disk', 'local');
    }

    /** $exercise null = soal mandiri (bank soal) yang dipakai evaluasi. */
    public function create(?Exercise $exercise, array $data, ?UploadedFile $audio = null): Question
    {
        $stored = null;

        try {
            return DB::transaction(function () use ($exercise, $data, $audio, &$stored) {
                $type = $data['question_type'];
                $question = new Question();
                $attrs = $this->attributesFor($question, $type, $data);

                if ($type === 'audio_choice' && $audio) {
                    $attrs['audio_path'] = $stored = $audio->store('questions/audio', $this->disk());
                }

                $question->forceFill($attrs + [
                    'exercise_id'   => $exercise?->id,
                    'question_type' => $type,
                    'sort_order'    => $exercise ? ((int) Question::where('exercise_id', $exercise->id)->max('sort_order')) + 1 : 0,
                ])->save();

                if (in_array($type, ['multiple_choice', 'audio_choice'], true)) {
                    $this->syncOptions($question, $data);
                }

                return $question;
            });
        } catch (Throwable $e) {
            if ($stored) {
                Storage::disk($this->disk())->delete($stored);
            }
            throw $e;
        }
    }

    public function update(Question $question, array $data, ?UploadedFile $audio = null): Question
    {
        $type = (string) Labels::value($question->question_type);
        $stored = null;
        $oldAudio = null;

        try {
            DB::transaction(function () use ($question, $data, $audio, $type, &$stored, &$oldAudio) {
                $attrs = $this->attributesFor($question, $type, $data);

                if ($type === 'audio_choice' && $audio) {
                    $oldAudio = $question->audio_path;
                    $attrs['audio_path'] = $stored = $audio->store('questions/audio', $this->disk());
                }

                $question->forceFill($attrs)->save();

                if (in_array($type, ['multiple_choice', 'audio_choice'], true)) {
                    $this->syncOptions($question, $data);
                }
            });
        } catch (Throwable $e) {
            if ($stored) {
                Storage::disk($this->disk())->delete($stored);
            }
            throw $e;
        }

        if ($oldAudio) {
            $this->deleteAudioIfUnused($oldAudio, $question->id);
        }

        return $question;
    }

    /** Soal yang sudah dijawab atau dipakai evaluasi tidak boleh dihapus. */
    public function isUsed(Question $question): bool
    {
        return DB::table('exercise_answers')->where('question_id', $question->id)->exists()
            || DB::table('evaluation_questions')->where('question_id', $question->id)->exists()
            || DB::table('evaluation_answers')->where('question_id', $question->id)->exists();
    }

    /**
     * Versi massal isUsed(): id soal (dari $ids) yang sudah dijawab atau dipakai evaluasi.
     * Tiga query total, berapa pun jumlah soalnya.
     *
     * @param iterable<int> $ids
     * @return array<int, int>
     */
    public function usedQuestionIds(iterable $ids): array
    {
        $ids = collect($ids)->values()->all();

        if ($ids === []) {
            return [];
        }

        return collect()
            ->merge(DB::table('exercise_answers')->whereIn('question_id', $ids)->distinct()->pluck('question_id'))
            ->merge(DB::table('evaluation_questions')->whereIn('question_id', $ids)->distinct()->pluck('question_id'))
            ->merge(DB::table('evaluation_answers')->whereIn('question_id', $ids)->distinct()->pluck('question_id'))
            ->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public function delete(Question $question): void
    {
        $audio = $question->audio_path;

        DB::transaction(function () use ($question) {
            QuestionOption::where('question_id', $question->id)->delete();
            $question->delete();
        });

        if ($audio) {
            $this->deleteAudioIfUnused($audio, $question->id);
        }
    }

    /** Latihan boleh dihapus hanya jika belum punya attempt dan soalnya belum dipakai. */
    public function exerciseCanBeDeleted(Exercise $exercise): bool
    {
        if (DB::table('exercise_attempts')->where('exercise_id', $exercise->id)->exists()) {
            return false;
        }

        return $this->usedQuestionIds(Question::where('exercise_id', $exercise->id)->pluck('id')) === [];
    }

    public function deleteExercise(Exercise $exercise): void
    {
        DB::transaction(function () use ($exercise) {
            Question::where('exercise_id', $exercise->id)->get()->each(fn ($q) => $this->delete($q));
            $exercise->delete();
        });
    }

    public function move(Question $question, string $direction): void
    {
        $ids = Question::where('exercise_id', $question->exercise_id)
            ->orderBy('sort_order')->orderBy('id')->pluck('id')->all();

        $i = array_search($question->id, $ids, true);
        $j = $direction === 'up' ? $i - 1 : $i + 1;

        if ($i === false || ! isset($ids[$j])) {
            return;
        }

        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];

        DB::transaction(function () use ($ids) {
            foreach ($ids as $n => $id) {
                Question::whereKey($id)->update(['sort_order' => $n + 1]);
            }
        });
    }

    private function attributesFor(Question $question, string $type, array $d): array
    {
        $attrs = match ($type) {
            'multiple_choice', 'audio_choice' => [
                'question_text' => $d['question_text'],
                'answer_key'    => $d['correct_option'],
                'points'        => (int) $d['points'],
                'passage_text'  => null,
                'keywords'      => null,
                'metadata'      => null,
            ],
            'text_analysis' => $this->analysisAttributes($d),
            'short_answer'  => [
                'question_text' => $d['question_text'],
                'answer_key'    => $d['answer_key'],
                'keywords'      => Labels::splitKeywords($d['keywords'] ?? ''),
                'points'        => (int) $d['points'],
                'passage_text'  => null,
                'metadata'      => null,
            ],
        };

        // Jenis Basa Rinengga untuk soal mandiri (dipakai rekomendasi materi di Tahap 9).
        if (array_key_exists('rinengga_type_id', $d)) {
            $meta = is_array($attrs['metadata'] ?? null) ? $attrs['metadata'] : [];
            if (filled($d['rinengga_type_id'])) {
                $meta['rinengga_type_id'] = (int) $d['rinengga_type_id'];
            }
            $attrs['metadata'] = $meta ?: null;
        }

        // Jika model tidak men-cast kolom ke array, simpan sebagai JSON string.
        foreach (['keywords', 'metadata'] as $field) {
            if (is_array($attrs[$field] ?? null) && ! $question->hasCast($field)) {
                $attrs[$field] = json_encode($attrs[$field]);
            }
        }

        return $attrs;
    }

    private function analysisAttributes(array $d): array
    {
        $rows = collect($d['analysis_rows'])->values()->map(fn ($r) => [
            'label'    => trim($r['label']),
            'keywords' => Labels::splitKeywords($r['keywords']),
            'points'   => (int) $r['points'],
        ])->all();

        return [
            'question_text' => $d['question_text'] ?? null,
            'passage_text'  => $d['passage_text'],
            'metadata'      => ['analysis_rows' => $rows],
            'points'        => array_sum(array_column($rows, 'points')), // total poin = jumlah poin baris
            'answer_key'    => null,
            'keywords'      => null,
        ];
    }

    private function syncOptions(Question $question, array $d): void
    {
        $correct = $d['correct_option'];
        $existing = QuestionOption::where('question_id', $question->id)->get()->keyBy('option_key');

        foreach (['A', 'B', 'C', 'D'] as $i => $key) {
            $text = trim((string) ($d['option_'.strtolower($key)] ?? ''));
            $option = $existing->get($key);

            if ($text === '') {
                if ($option) {
                    if ($this->optionIsUsed($option)) {
                        throw ValidationException::withMessages([
                            'option_'.strtolower($key) => 'Opsi iki wis dipilih siswa, ora bisa dikosongake.',
                        ]);
                    }
                    $option->delete();
                }
                continue;
            }

            ($option ?? new QuestionOption())->forceFill([
                'question_id' => $question->id,
                'option_key'  => $key,
                'option_text' => $text,
                'is_correct'  => $key === $correct,
                'sort_order'  => $i + 1,
            ])->save();
        }
    }

    private function optionIsUsed(QuestionOption $option): bool
    {
        return DB::table('exercise_answers')->where('selected_option_id', $option->id)->exists()
            || DB::table('evaluation_answers')->where('selected_option_id', $option->id)->exists();
    }

    private function deleteAudioIfUnused(string $path, int $exceptQuestionId): void
    {
        if (! Question::where('id', '!=', $exceptQuestionId)->where('audio_path', $path)->exists()) {
            Storage::disk($this->disk())->delete($path);
        }
    }
}
