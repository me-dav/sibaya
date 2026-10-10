<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\Support\MakesPuzzles;
use Tests\TestCase;

/**
 * Sapuan otomatis: SETIAP route ber-nama "admin.*" (termasuk yang dibuat di tahap mendatang)
 * harus mengalihkan tamu ke login dan menjawab 403 untuk siswa. Route admin baru yang lupa
 * dipasangi middleware akan langsung tertangkap di sini.
 */
class AuthorizationSweepTest extends TestCase
{
    use MakesEvaluations, MakesExercises, MakesMaterials, MakesPuzzles, RefreshDatabase;

    /** @return array<int, array{0: string, 1: string, 2: string}> [method, uri, name] */
    private function adminRoutes(): array
    {
        $siswa = $this->siswa();
        $exercise = $this->makeExercise();
        $evaluation = $this->makeEvaluation();
        $this->makeMc($evaluation);

        $values = [
            'material'          => $this->makeMaterial($this->makeType())->id,
            'student'           => $siswa->id,
            'exercise'          => $exercise->id,
            'question'          => $this->makeQuestion($exercise)->id,
            'evaluation'        => $evaluation->id,
            'puzzle'            => $this->makePuzzle('susun_kata', $this->scrambleConfig())->id,
            'exerciseAttempt'   => $this->makeAttempt($siswa->id, $exercise->id),
            'evaluationAttempt' => $this->makeEvalAttempt($siswa, $evaluation)->id,
        ];

        $routes = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name || ! str_starts_with($name, 'admin.')) {
                continue;
            }

            $uri = '/'.ltrim($route->uri(), '/');
            foreach ($route->parameterNames() as $param) {
                $this->assertArrayHasKey($param, $values, "Parameter {{$param}} pada route {$name} belum dipetakan di AuthorizationSweepTest");
                $uri = preg_replace('/\{'.preg_quote($param, '/').'\??\}/', (string) $values[$param], $uri);
            }

            $method = collect($route->methods())->first(fn ($m) => $m !== 'HEAD');
            $routes[] = [$method, $uri, $name];
        }

        return $routes;
    }

    public function test_tamu_dialihkan_ke_login_pada_semua_route_admin(): void
    {
        $routes = $this->adminRoutes();
        $this->assertGreaterThan(40, count($routes), 'Terlalu sedikit route admin terdeteksi, periksa routes/admin.php');

        $failures = [];
        foreach ($routes as [$method, $uri, $name]) {
            $res = $this->call($method, $uri);
            if (! ($res->isRedirect() && str_contains((string) $res->headers->get('Location'), 'login'))) {
                $failures[] = "TAMU {$method} {$uri} ({$name}) -> {$res->getStatusCode()}";
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_siswa_ditolak_403_pada_semua_route_admin(): void
    {
        $routes = $this->adminRoutes();
        $siswa = $this->siswa();

        $failures = [];
        foreach ($routes as [$method, $uri, $name]) {
            $res = $this->actingAs($siswa)->call($method, $uri);
            if ($res->getStatusCode() !== 403) {
                $failures[] = "SISWA {$method} {$uri} ({$name}) -> {$res->getStatusCode()}";
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_route_siswa_yang_butuh_login_mengalihkan_tamu(): void
    {
        $paths = ['/dashboard', '/rekomendasi/1', '/sertifikat/1', '/sertifikat/1/unduh', '/evaluasi/percobaan/1', '/wahana-teka-teki/percobaan/1'];

        foreach ($paths as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }

        foreach (['/evaluasi/1/start', '/evaluasi/percobaan/1/submit', '/evaluasi/percobaan/1/sertifikat', '/wahana-teka-teki/percobaan/1/submit'] as $path) {
            $this->post($path)->assertRedirect(route('login'));
        }
    }
}
