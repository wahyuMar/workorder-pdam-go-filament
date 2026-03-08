<?php

namespace Database\Factories;

use App\Models\KlasifikasiSr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KlasifikasiSr>
 */
class KlasifikasiSrFactory extends Factory
{
    protected $model = KlasifikasiSr::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'price' => fake()->randomFloat(2, 50000, 500000),
        ];
    }
}
