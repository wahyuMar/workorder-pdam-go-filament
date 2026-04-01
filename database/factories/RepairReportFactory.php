<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\RepairReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepairReport>
 */
class RepairReportFactory extends Factory
{
    protected $model = RepairReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_bap' => 'BAP-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'complaint_id' => Complaint::factory(),
            'no_sambungan' => fake()->numerify('######'),
            'nama' => fake()->name(),
            'alamat' => fake()->address(),
            'lokasi' => fake()->streetAddress(),
            'foto_sebelum' => null,
            'foto_sesudah' => null,
            'items' => [
                [
                    'item_type' => 'Barang',
                    'material_and_service_id' => null,
                    'material_name' => fake()->word(),
                    'quantity' => 1,
                    'unit' => 'pcs',
                ],
            ],
            'catatan' => fake()->sentence(),
            'tanggal' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
