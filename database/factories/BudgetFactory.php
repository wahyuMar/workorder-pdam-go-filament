<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budgeting_number' => 'RAB-' . fake()->unique()->numerify('########'),
            'survey_id' => Survey::factory(),
            'date' => fake()->date(),
            'blueprint' => null,
            'total_amount' => fake()->randomFloat(2, 500000, 10000000),
            'created_by' => User::factory(),
        ];
    }
}
