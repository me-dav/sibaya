<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_siswa_cannot_open_admin_dashboard(): void
    {
        $siswa = User::factory()->create();

        $this->actingAs($siswa)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_admin_is_redirected_from_dashboard_to_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');
    }

    public function test_siswa_can_open_student_dashboard(): void
    {
        $siswa = User::factory()->create();

        $this->actingAs($siswa)->get('/dashboard')->assertOk();
    }

    public function test_inactive_user_is_logged_out(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
