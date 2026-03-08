<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\SubscriptionCancellation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionCancellation>
 */
class SubscriptionCancellationFactory extends Factory
{
    protected $model = SubscriptionCancellation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_bacl' => 'BACL-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'lokasi' => fake()->streetAddress(),
            'foto_sebelum' => null,
            'foto_sesudah' => null,
            'catatan' => fake()->sentence(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
