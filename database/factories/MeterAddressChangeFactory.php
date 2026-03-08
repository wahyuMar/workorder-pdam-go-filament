<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\MeterAddressChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterAddressChange>
 */
class MeterAddressChangeFactory extends Factory
{
    protected $model = MeterAddressChange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_spua' => 'SPUA-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'pegawai_id' => null,
            'nama_pegawai' => fake()->name(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'id_unit_lama' => null,
            'nama_unit_lama' => fake()->word(),
            'id_rt_rw_lama' => null,
            'id_wilayah_lama' => null,
            'id_jalan_lama' => null,
            'id_kolektor_lama' => null,
            'id_unit_baru' => null,
            'id_rt_rw_baru' => null,
            'id_wilayah_baru' => null,
            'id_jalan_baru' => null,
            'id_kolektor_baru' => null,
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'biaya_ubah_alamat' => fake()->randomFloat(2, 25000, 200000),
            'alasan_ubah_alamat' => fake()->sentence(),
            'upload_ktp' => null,
            'upload_kk' => null,
            'is_confirmed' => fake()->boolean(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the address change is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_confirmed' => true,
        ]);
    }
}
