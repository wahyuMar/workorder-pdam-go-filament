<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\MeterTera;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeterTeraTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_meter_tera_and_auto_generate_no_spp(): void
    {
        $complaint = Complaint::factory()->create();
        $user = User::factory()->create();

        $meterTera = MeterTera::create([
            'complaint_id' => $complaint->id,
            'pegawai_id' => $user->id,
            'nama_pegawai' => $user->name,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
            'latitude' => $complaint->latitude,
            'longitude' => $complaint->longitude,
            'keluhan' => $complaint->isi_pengaduan,
            'hasil_tera_meter' => 'Akurasi meter dalam batas toleransi.',
            'tanggal' => now(),
        ]);

        $this->assertNotNull($meterTera->no_spp);
        $this->assertStringStartsWith('SPP-', $meterTera->no_spp);

        $this->assertDatabaseHas('meter_teras', [
            'id' => $meterTera->id,
            'complaint_id' => $complaint->id,
            'no_spp' => $meterTera->no_spp,
        ]);
    }
}
