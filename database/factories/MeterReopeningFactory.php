<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterReopening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterReopening>
 */
class MeterReopeningFactory extends Factory
{
    protected $model = MeterReopening::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_spbk' => 'SPBK-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'pegawai_id' => User::factory(),
            'nama_pegawai' => fake()->name(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'alasan_buka_kembali' => fake()->sentence(),
            'biaya_buka_kembali' => fake()->randomFloat(2, 50000, 300000),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
