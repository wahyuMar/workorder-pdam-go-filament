<?php

namespace Database\Factories;

use App\Enums\WorkOrderEnum;
use App\Models\Complaint;
use App\Models\ComplaintFollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComplaintFollowUp>
 */
class ComplaintFollowUpFactory extends Factory
{
    protected $model = ComplaintFollowUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'complaint_number' => 'PGD-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'carbon_copies' => [fake()->name(), fake()->name()],
            'work_order' => fake()->randomElement(WorkOrderEnum::cases()),
            'notes' => fake()->paragraph(),
            'photos' => null,
            'follow_up_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
