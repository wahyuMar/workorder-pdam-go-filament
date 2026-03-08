<?php

namespace Database\Factories;

use App\Models\CustomerRegistration;
use App\Models\District;
use App\Models\Program;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerRegistration>
 */
class CustomerRegistrationFactory extends Factory
{
    protected $model = CustomerRegistration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_surat' => fake()->unique()->numerify('SR-########'),
            'nama_lengkap' => fake()->name(),
            'program_id' => Program::factory(),
            'no_ktp' => fake()->numerify('################'),
            'no_kk' => fake()->numerify('################'),
            'alamat_ktp' => fake()->address(),
            'dusun_kampung_ktp' => fake()->citySuffix(),
            'rt_ktp' => fake()->numberBetween(1, 20),
            'rw_ktp' => fake()->numberBetween(1, 15),
            'kel_desa_ktp' => fake()->city(),
            'kecamatan_ktp' => fake()->city(),
            'kab_kota_ktp' => fake()->city(),
            'province_id_ktp' => Province::factory(),
            'regency_id_ktp' => Regency::factory(),
            'district_id_ktp' => District::factory(),
            'village_id_ktp' => Village::factory(),
            'pekerjaan' => fake()->jobTitle(),
            'email' => fake()->safeEmail(),
            'no_telp' => fake()->numerify('0##########'),
            'no_hp' => fake()->numerify('08##########'),
            'alamat_pasang' => fake()->address(),
            'dusun_kampung_pasang' => fake()->citySuffix(),
            'rt_pasang' => fake()->numberBetween(1, 20),
            'rw_pasang' => fake()->numberBetween(1, 15),
            'kel_desa_pasang' => fake()->city(),
            'kecamatan_pasang' => fake()->city(),
            'kab_kota_pasang' => fake()->city(),
            'province_id_pasang' => Province::factory(),
            'regency_id_pasang' => Regency::factory(),
            'district_id_pasang' => District::factory(),
            'village_id_pasang' => Village::factory(),
            'jumlah_penghuni_tetap' => fake()->numberBetween(1, 10),
            'jumlah_penghuni_tidak_tetap' => fake()->numberBetween(0, 5),
            'jumlah_kran_air_minum' => fake()->numberBetween(1, 5),
            'jenis_rumah' => fake()->randomElement(['permanen', 'semi_permanen', 'non_permanen']),
            'jumlah_kran' => fake()->numberBetween(1, 10),
            'daya_listrik' => fake()->randomElement([450, 900, 1300, 2200]),
            'upload_ktp' => null,
            'upload_kk' => null,
            'upload_tagihan_listrik' => null,
            'upload_foto_rumah' => null,
            'longitude' => fake()->longitude(106.0, 112.0),
            'latitude' => fake()->latitude(-8.5, -6.5),
            'tanggal' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
