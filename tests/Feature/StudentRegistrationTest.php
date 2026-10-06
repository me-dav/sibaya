<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Sri Rahayu',
            'email' => 'sri@example.test',
            'class_name' => 'X-3',
            'school_name' => 'SMA Negeri 1 Surabaya',
            'password' => 'password-kuat-123',
            'password_confirmation' => 'password-kuat-123',
        ], $override);
    }

    public function test_siswa_can_register_with_class_and_school(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'sri@example.test',
            'class_name' => 'X-3',
            'school_name' => 'SMA Negeri 1 Surabaya',
            'role' => 'siswa',
        ]);
    }

    public function test_registration_requires_class_and_school(): void
    {
        $this->post('/register', $this->payload(['class_name' => '', 'school_name' => '']))
            ->assertSessionHasErrors(['class_name', 'school_name']);

        $this->assertGuest();
    }

    public function test_role_cannot_be_forced_to_admin_via_request(): void
    {
        $this->post('/register', $this->payload(['role' => 'admin']));

        $this->assertSame(UserRole::SISWA, User::first()->role);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
