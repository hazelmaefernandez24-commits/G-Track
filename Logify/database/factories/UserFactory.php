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
        return [
            'user_id' => $this->faker->unique()->userName(),
            'user_fname' => $this->faker->firstName(),
            'user_lname' => $this->faker->lastName(),
            'user_mInitial' => $this->faker->randomLetter(),
            'user_suffix' => $this->faker->randomElement(['Jr.', 'Sr.', 'III']),
            'user_email' => $this->faker->unique()->safeEmail(),
            'user_password' => Hash::make('password'),
            'user_role' => $this->faker->randomElement(['admin', 'student', 'teacher']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'is_temp_password' => false,
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
