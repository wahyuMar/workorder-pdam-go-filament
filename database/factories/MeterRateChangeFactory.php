<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterRateChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterRateChange>
 */
class MeterRateChangeFactory extends Factory
{
    protected $model = MeterRateChange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_sput' => 'SPUT-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'email' => fake()->safeEmail(),
            'no_hp' => fake()->numerify('08##########'),
            'no_ktp' => fake()->numerify('################'),
            'alasan_ganti_tarif' => fake()->sentence(),
            'is_confirmed' => fake()->boolean(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the rate change is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_confirmed' => true,
        ]);
    }
}
