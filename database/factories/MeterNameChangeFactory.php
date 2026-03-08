<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterNameChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterNameChange>
 */
class MeterNameChangeFactory extends Factory
{
    protected $model = MeterNameChange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_spun' => 'SPUN-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'pegawai_id' => null,
            'nama_pegawai' => fake()->name(),
            'no_sambungan' => fake()->numerify('######'),
            'nama_lama' => fake()->name(),
            'nama_baru' => fake()->name(),
            'alamat_lama' => fake()->address(),
            'alamat_baru' => fake()->address(),
            'email_lama' => fake()->safeEmail(),
            'email_baru' => fake()->safeEmail(),
            'no_hp_lama' => fake()->numerify('08##########'),
            'no_hp_baru' => fake()->numerify('08##########'),
            'no_ktp_lama' => fake()->numerify('################'),
            'no_ktp_baru' => fake()->numerify('################'),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'alasan_ubah_nama' => fake()->sentence(),
            'upload_ktp' => null,
            'upload_kk' => null,
            'is_confirmed' => fake()->boolean(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the name change is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_confirmed' => true,
        ]);
    }
}
