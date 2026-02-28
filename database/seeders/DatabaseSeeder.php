<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Program;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use App\UserRoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();
            // User::factory()
            //     ->count(2)
            //     ->state(
            //         new Sequence(
            //             [
            //                 'name' => 'Admin User',
            //                 'email' => 'admin@example.com',
            //                 'role' => UserRoleEnum::ADMIN->value
            //             ],
            //             [
            //                 'name' => 'Regular User',
            //                 'email' => 'user@example.com',
            //                 'role' => UserRoleEnum::USER->value
            //             ],
            //         )
            //     )
            //     ->create([
            //         'password' => Hash::make('password'),
            //     ]);

            // Program::factory(10)->create(['is_active' => true]);

            Province::factory()
                ->has(
                    Regency::factory()
                        ->state(
                            new Sequence(
                                [
                                    'is_selectable' => true
                                ],
                                [
                                    'is_selectable' => false
                                ],
                            )
                        )
                        ->has(
                            District::factory()
                                ->has(
                                    Village::factory()->count(5),
                                )
                                ->count(3),
                        )
                        ->count(2),
                )
                ->count(2)
                ->state(
                    new Sequence(
                        [
                            'is_selectable' => true
                        ],
                        [
                            'is_selectable' => false
                        ],
                    )
                )->create();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }
}
