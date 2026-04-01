<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\MaterialAndService;
use App\Models\MeterTera;
use App\Models\TeraMeterReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeraMeterReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_tera_meter_report_with_material_and_services_items(): void
    {
        $complaint = Complaint::factory()->create();

        MeterTera::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
        ]);

        $barang = MaterialAndService::factory()->create([
            'name' => 'Segel Meter',
            'unit' => 'buah',
            'is_service' => false,
        ]);

        $jasa = MaterialAndService::factory()->service()->create([
            'name' => 'Jasa Tera Meter',
            'unit' => 'jasa',
        ]);

        $alat = MaterialAndService::factory()->create([
            'name' => 'Alat Uji Tekanan',
            'unit' => 'unit',
            'is_service' => false,
        ]);

        $report = TeraMeterReport::create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
            'lokasi' => 'Rumah Pelanggan',
            'items' => [
                [
                    'item_type' => 'Barang',
                    'material_and_service_id' => $barang->id,
                    'material_name' => $barang->name,
                    'quantity' => 1,
                    'unit' => $barang->unit,
                ],
                [
                    'item_type' => 'Jasa',
                    'material_and_service_id' => $jasa->id,
                    'material_name' => $jasa->name,
                    'quantity' => 1,
                    'unit' => $jasa->unit,
                ],
                [
                    'item_type' => 'Alat',
                    'material_and_service_id' => $alat->id,
                    'material_name' => $alat->name,
                    'quantity' => 1,
                    'unit' => $alat->unit,
                ],
            ],
            'tanggal' => now(),
        ]);

        $this->assertNotNull($report->no_bap);
        $this->assertStringStartsWith('BAP-', $report->no_bap);
        $this->assertCount(3, $report->items);

        $this->assertDatabaseHas('tera_meter_reports', [
            'id' => $report->id,
            'complaint_id' => $complaint->id,
            'no_bap' => $report->no_bap,
        ]);
    }
}
