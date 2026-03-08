<?php

namespace Database\Factories;

use App\Enums\MaterialAndServiceCategory;
use App\Models\MaterialAndService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaterialAndService>
 */
class MaterialAndServiceFactory extends Factory
{
    protected $model = MaterialAndService::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement(MaterialAndServiceCategory::cases()),
            'unit' => fake()->randomElement(['pcs', 'meter', 'set', 'buah', 'lonjor']),
            'is_deletable' => true,
            'is_service' => fake()->boolean(30),
            'price' => fake()->randomFloat(2, 10000, 500000),
        ];
    }

    /**
     * Indicate that the material is a service.
     */
    public function service(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_service' => true,
        ]);
    }
}
