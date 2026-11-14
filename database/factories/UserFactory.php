<?php

namespace Database\Factories;

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'user_type' => UserType::Staff,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([UserRole::SupportAgent->value]);
        });
    }

    public function superAdmin(): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles([UserRole::SuperAdmin->value]));
    }

    public function teamLead(): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles([UserRole::TeamLead->value]));
    }

    public function serviceAccount(): static
    {
        return $this->state(fn (array $attributes) => ['user_type' => UserType::Service]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
