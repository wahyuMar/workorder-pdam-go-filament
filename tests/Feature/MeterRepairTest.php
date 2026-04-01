<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\MeterRepair;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeterRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_meter_repair_and_auto_generate_no_spp(): void
    {
        $complaint = Complaint::factory()->create();
        $user = User::factory()->create();

        $meterRepair = MeterRepair::create([
            'complaint_id' => $complaint->id,
            'pegawai_id' => $user->id,
            'nama_pegawai' => $user->name,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
            'latitude' => $complaint->latitude,
            'longitude' => $complaint->longitude,
            'keluhan' => $complaint->isi_pengaduan,
            'tindakan_perbaikan' => 'Perbaikan sambungan pipa di titik bocor.',
            'tanggal' => now(),
        ]);

        $this->assertNotNull($meterRepair->no_spp);
        $this->assertStringStartsWith('SPP-', $meterRepair->no_spp);

        $this->assertDatabaseHas('meter_repairs', [
            'id' => $meterRepair->id,
            'complaint_id' => $complaint->id,
            'no_spp' => $meterRepair->no_spp,
        ]);
    }
}
