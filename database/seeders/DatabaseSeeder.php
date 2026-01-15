<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()
            ->count(2)
            ->state(
                new Sequence(
                    [
                        'name' => 'Admin User',
                        'email' => 'admin@example.com',
                        'role' => UserRoleEnum::ADMIN->value
                    ],
                    [
                        'name' => 'Regular User',
                        'email' => 'user@example.com',
                        'role' => UserRoleEnum::USER->value
                    ],
                )
            )
            ->create([
                'password' => Hash::make('password'),
            ]);
    }
}
