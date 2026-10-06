<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::SISWA,
            'class_name' => 'X-' . fake()->numberBetween(1, 8),
            'school_name' => 'SMA Negeri ' . fake()->numberBetween(1, 20) . ' Surabaya',
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::ADMIN,
            'class_name' => null,
            'school_name' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
