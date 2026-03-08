<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\SubscriptionReopening;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionReopening>
 */
class SubscriptionReopeningFactory extends Factory
{
    protected $model = SubscriptionReopening::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_bast_bk' => 'BAST-BK-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
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
