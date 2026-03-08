<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_pengaduan' => 'PGD-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'complaint_type_id' => ComplaintType::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'longitude' => fake()->longitude(106.0, 112.0),
            'email' => fake()->safeEmail(),
            'no_hp' => fake()->numerify('08##########'),
            'no_ktp' => fake()->numerify('################'),
            'sumber' => fake()->randomElement(['telepon', 'whatsapp', 'datang_langsung', 'website']),
            'judul_pengaduan' => fake()->sentence(),
            'isi_pengaduan' => fake()->paragraph(),
            'foto' => null,
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement(['baru', 'proses', 'selesai']),
            'priority' => fake()->randomElement(['rendah', 'sedang', 'tinggi']),
        ];
    }
}
