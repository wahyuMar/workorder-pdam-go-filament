<?php

namespace Tests\Feature;

use App\Models\CustomerRegistration;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class CustomerRegistrationProcessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_customer_registration()
    {
        $program = Program::factory()->create(['is_active' => true]);

        $data = [
            'nama_lengkap' => 'Test User',
            'program_id' => $program->id,
            'no_ktp' => '1234567890123456',
            'no_kk' => '6543210987654321',
            'alamat_ktp' => 'Jl. KTP',
            'dusun_kampung_ktp' => 'Dusun A',
            'rt_ktp' => 1,
            'rw_ktp' => 2,
            'kel_desa_ktp' => 'Kelurahan A',
            'kecamatan_ktp' => 'Kecamatan A',
            'kab_kota_ktp' => 'Kabupaten A',
            'province_id_ktp' => 1,
            'regency_id_ktp' => 1,
            'district_id_ktp' => 1,
            'village_id_ktp' => 1,
            'pekerjaan' => 'Karyawan',
            'email' => 'test@example.com',
            'no_telp' => '0211234567',
            'no_hp' => '08123456789',
            'alamat_pasang' => 'Jl. Pasang',
            'dusun_kampung_pasang' => 'Dusun B',
            'rt_pasang' => 3,
            'rw_pasang' => 4,
            'kel_desa_pasang' => 'Kelurahan B',
            'kecamatan_pasang' => 'Kecamatan B',
            'kab_kota_pasang' => 'Kabupaten B',
            'province_id_pasang' => 1,
            'regency_id_pasang' => 1,
            'district_id_pasang' => 1,
            'village_id_pasang' => 1,
            'jumlah_penghuni_tetap' => 2,
            'jumlah_penghuni_tidak_tetap' => 0,
            'jumlah_kran_air_minum' => 1,
            'jenis_rumah' => 'Rumah',
            'jumlah_kran' => 2,
            'daya_listrik' => 900,
            'longitude' => 106.816666,
            'latitude' => -6.200000,
        ];

        $registration = CustomerRegistration::create($data);

        $this->assertDatabaseHas('customer_registrations', [
            'nama_lengkap' => 'Test User',
            'no_ktp' => '1234567890123456',
        ]);
        $this->assertNotNull($registration->no_surat);
        $this->assertNotNull($registration->tanggal);
    }

    /** @test */
    public function registration_has_one_survey_relation()
    {
        $registration = CustomerRegistration::factory()->create();
        $this->assertTrue(method_exists($registration, 'survey'));
        $this->assertNull($registration->survey);
    }
}
