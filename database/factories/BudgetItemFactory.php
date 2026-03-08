<?php

namespace Database\Factories;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use App\Models\Budget;
use App\Models\BudgetItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetItem>
 */
class BudgetItemFactory extends Factory
{
    protected $model = BudgetItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = fake()->randomElement(BudgetItemCategory::cases());
        $subCategories = $category->getSubCategories();
        $subCategory = fake()->randomElement($subCategories);
        $quantity = fake()->numberBetween(1, 20);
        $price = fake()->randomFloat(2, 5000, 200000);

        return [
            'budget_id' => Budget::factory(),
            'category' => $category,
            'sub_category' => $subCategory,
            'name' => fake()->words(3, true),
            'unit' => fake()->randomElement(['pcs', 'meter', 'set', 'buah', 'lonjor']),
            'quantity' => $quantity,
            'price' => $price,
            'item_amount' => $quantity * $price,
        ];
    }
}
