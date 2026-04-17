<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
<<<<<<< HEAD
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
=======
>>>>>>> origin/thainguyen

class UserFactory extends Factory
{
    protected static ?string $password;

<<<<<<< HEAD
=======
    /**
     * @return array<string, mixed>
     */
>>>>>>> origin/thainguyen
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
<<<<<<< HEAD
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'role' => 'customer',
            'status' => true,
            'remember_token' => Str::random(10),

        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            // bỏ trống
        ]);
    }
=======
            'password' => static::$password ??= 'password',
            'phone' => null,
            'role' => 'customer',
            'status' => true,
        ];
    }
>>>>>>> origin/thainguyen
}
