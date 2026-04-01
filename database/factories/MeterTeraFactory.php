<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterTera;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterTera>
 */
class MeterTeraFactory extends Factory
{
    protected $model = MeterTera::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_spp' => 'SPP-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'pegawai_id' => User::factory(),
            'nama_pegawai' => fake()->name(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'keluhan' => fake()->sentence(),
            'hasil_tera_meter' => fake()->paragraph(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
