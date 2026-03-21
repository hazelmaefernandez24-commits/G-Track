<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        $name = fake()->name();
        $nameParts = explode(' ', $name);

        return [
            'user_id' => 'USR' . fake()->unique()->numberBetween(1000, 9999),
            'user_fname' => $nameParts[0],
            'user_lname' => $nameParts[1] ?? '',
            'user_email' => fake()->unique()->safeEmail(),
            'user_password' => static::$password ??= Hash::make('password'),
            'user_role' => 'student',
            'gender' => fake()->randomElement(['M', 'F']),
            'status' => 'active',
            'is_temp_password' => false,
            'token' => Str::random(10),
        ];
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
