<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
>>>>>>> d5502fc (Tạo tài khoản)

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
<<<<<<< HEAD
     * The current password being used by the factory (stored plain; model cast hashes it).
=======
     * The current password being used by the factory.
>>>>>>> d5502fc (Tạo tài khoản)
     */
    protected static ?string $password;

    /**
<<<<<<< HEAD
=======
     * Define the model's default state.
     *
>>>>>>> d5502fc (Tạo tài khoản)
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
<<<<<<< HEAD
            'role_id' => 1,
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= 'password',
            'phone' => null,
            'is_active' => true,
        ];
    }
=======
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
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
>>>>>>> d5502fc (Tạo tài khoản)
}
