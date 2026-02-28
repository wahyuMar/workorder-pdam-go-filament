<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_survey' => 'SRV-' . fake()->unique()->numberBetween(10000, 99999),
            'lokasi_pipa_distribusi_lat' => fake()->latitude(-10, 6),
            'lokasi_pipa_distribusi_long' => fake()->longitude(95, 141),
            'panjang_pipa_sr' => fake()->numberBetween(5, 100),
            'ukuran_clamp_sadel' => fake()->randomElement(['1/2 inch', '3/4 inch', '1 inch', '1.5 inch', '2 inch']),
            'lokasi_sr_lat' => fake()->latitude(-10, 6),
            'lokasi_sr_long' => fake()->longitude(95, 141),
            'foto_rumah' => 'surveys/foto-rumah/' . fake()->uuid() . '.jpg',
            'foto_penghuni' => 'surveys/foto-penghuni/' . fake()->uuid() . '.jpg',
            'foto_lokasi_wm' => 'surveys/foto-lokasi-wm/' . fake()->uuid() . '.jpg',
            'lokasi_rabatan_lat' => fake()->latitude(-10, 6),
            'lokasi_rabatan_long' => fake()->longitude(95, 141),
            'panjang_rabatan' => fake()->numberBetween(5, 50),
            'lokasi_crossing_lat' => fake()->latitude(-10, 6),
            'lokasi_crossing_long' => fake()->longitude(95, 141),
            'panjang_crossing' => fake()->numberBetween(3, 30),
            'jenis_crossing' => fake()->randomElement(['Jalan Aspal', 'Jalan Beton', 'Jalan Tanah', 'Sungai', 'Selokan']),
            'klasifikasi_sr' => fake()->randomElement(['SR Rumah Tinggal', 'SR Komersial', 'SR Industri', 'SR Sosial']),
            'tanggal_survey' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
