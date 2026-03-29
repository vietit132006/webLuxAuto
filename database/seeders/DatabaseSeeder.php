<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        $this->call(VehicleSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'full_name' => 'Test User',
=======
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
>>>>>>> d5502fc (Tạo tài khoản)
            'email' => 'test@example.com',
        ]);
    }
}
