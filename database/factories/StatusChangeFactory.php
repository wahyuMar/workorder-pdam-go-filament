<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\StatusChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusChange>
 */
class StatusChangeFactory extends Factory
{
    protected $model = StatusChange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_baus' => 'BAUS-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'lokasi' => fake()->streetAddress(),
            'jenis_rumah' => fake()->randomElement(['permanen', 'semi_permanen', 'non_permanen']),
            'jumlah_kran' => fake()->numberBetween(1, 10),
            'daya_listrik' => fake()->randomElement([450, 900, 1300, 2200]),
            'verifikasi_ktp' => null,
            'verifikasi_kk' => null,
            'verifikasi_tagihan_listrik' => null,
            'verifikasi_foto_rumah' => null,
            'klasifikasi_sr' => fake()->word(),
            'catatan' => fake()->sentence(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
