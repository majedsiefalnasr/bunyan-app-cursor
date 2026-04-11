<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'role' => UserRole::Customer->value,
            'phone' => fake()->optional(0.6)->numerify('+9665#######'),
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Customer->value,
        ]);
    }

    public function contractor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Contractor->value,
        ]);
    }

    public function supervisingArchitect(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SupervisingArchitect->value,
        ]);
    }

    public function fieldEngineer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::FieldEngineer->value,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
