<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterReplacement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterReplacement>
 */
class MeterReplacementFactory extends Factory
{
    protected $model = MeterReplacement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_spgm' => 'SPGM-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'pegawai_id' => User::factory(),
            'nama_pegawai' => fake()->name(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'alasan_penggantian' => fake()->sentence(),
            'biaya_ganti_meter' => fake()->randomFloat(2, 50000, 500000),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
