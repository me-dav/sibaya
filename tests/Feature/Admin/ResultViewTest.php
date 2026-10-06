<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class ResultViewTest extends TestCase
{
    use MakesExercises, MakesMaterials, RefreshDatabase;

    public function test_admin_melihat_daftar_dan_detail_hasil(): void
    {
        $siswa = $this->siswa();
        $e = $this->makeExercise();
        $q = $this->makeQuestion($e, ['question_text' => 'Pitakon siji', 'answer_key' => 'bener']);
        $attempt = $this->makeAttempt($siswa->id, $e->id, ['score' => 75]);
        DB::table('exercise_answers')->insert([
            'exercise_attempt_id' => $attempt, 'question_id' => $q->id, 'answer_text' => 'salah',
            'is_correct' => false, 'points_earned' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.results.index'))->assertOk()->assertSee($siswa->name)->assertSee('75,0');
        $this->actingAs($admin)->get(route('admin.results.exercises.show', $attempt))
            ->assertOk()->assertSee('Pitakon siji')->assertSee('salah')->assertSee('bener')->assertSee('Salah');
    }

    public function test_siswa_tidak_dapat_melihat_hasil(): void
    {
        $siswa = $this->siswa();
        $attempt = $this->makeAttempt($siswa->id, $this->makeExercise()->id);

        $this->actingAs($siswa)->get(route('admin.results.index'))->assertForbidden();
        $this->actingAs($siswa)->get(route('admin.results.exercises.show', $attempt))->assertForbidden();
    }
}
