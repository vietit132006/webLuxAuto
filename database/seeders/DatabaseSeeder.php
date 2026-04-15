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
        $this->call(CarSeeder::class);
        $this->call([UserSeeder::class]);
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->make(['email' => 'test@example.com'])->toArray()
        );
    }
}
