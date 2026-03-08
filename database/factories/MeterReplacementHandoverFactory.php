<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterReplacementHandover;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterReplacementHandover>
 */
class MeterReplacementHandoverFactory extends Factory
{
    protected $model = MeterReplacementHandover::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_bast_gm' => 'BAST-GM-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'lokasi' => fake()->streetAddress(),
            'foto_sebelum' => null,
            'foto_sesudah' => null,
            'merk_wm_lama' => fake()->randomElement(['Itron', 'Zenner', 'Sensus', 'Elster']),
            'no_wm_lama' => fake()->numerify('WM-######'),
            'merk_wm_baru' => fake()->randomElement(['Itron', 'Zenner', 'Sensus', 'Elster']),
            'no_wm_baru' => fake()->numerify('WM-######'),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
