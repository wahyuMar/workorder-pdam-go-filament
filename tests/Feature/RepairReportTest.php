<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\MaterialAndService;
use App\Models\MeterRepair;
use App\Models\RepairReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_repair_report_with_material_and_services_items(): void
    {
        $complaint = Complaint::factory()->create();

        MeterRepair::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
        ]);

        $barang = MaterialAndService::factory()->create([
            'name' => 'Pipa 1/2',
            'unit' => 'meter',
            'is_service' => false,
        ]);

        $jasa = MaterialAndService::factory()->service()->create([
            'name' => 'Jasa Perbaikan Kebocoran',
            'unit' => 'jasa',
        ]);

        $alat = MaterialAndService::factory()->create([
            'name' => 'Kunci Pipa',
            'unit' => 'buah',
            'is_service' => false,
        ]);

        $report = RepairReport::create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => $complaint->alamat,
            'lokasi' => 'Blok A-12',
            'items' => [
                [
                    'item_type' => 'Barang',
                    'material_and_service_id' => $barang->id,
                    'material_name' => $barang->name,
                    'quantity' => 2,
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

        $this->assertDatabaseHas('repair_reports', [
            'id' => $report->id,
            'complaint_id' => $complaint->id,
            'no_bap' => $report->no_bap,
        ]);
    }
}
