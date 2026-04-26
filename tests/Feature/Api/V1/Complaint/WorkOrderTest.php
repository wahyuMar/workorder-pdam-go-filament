<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\MeterClosed;
use App\Models\MeterDisconnection;
use App\Models\MeterNameChange;
use App\Models\MeterRateChange;
use App\Models\MeterReopening;
use App\Models\MeterRepair;
use App\Models\MeterReplacement;
use App\Models\MeterTera;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabaseCompat;

    private ComplaintType $complaintType;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
        $this->complaintType = ComplaintType::create(['name' => 'Kebocoran Pipa', 'is_active' => true]);
    }

    private function createCustomerUser(array $overrides = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ], $overrides));

        $user->assignRole('customer');

        return $user;
    }

    private function createComplaint(User $user, array $overrides = []): Complaint
    {
        $complaint = Complaint::create(array_merge([
            'user_id' => $user->id,
            'complaint_type_id' => $this->complaintType->id,
            'no_sambungan' => '01PNRG0001',
            'nama' => 'Test Customer',
            'judul_pengaduan' => 'Air Tidak Mengalir',
            'isi_pengaduan' => 'Sudah 2 hari air tidak mengalir.',
            'sumber' => 'mobile_apps',
            'tanggal' => now(),
        ], $overrides));

        $complaint->refresh();

        return $complaint;
    }

    private function getWorkOrderEvent(User $user, Complaint $complaint): ?array
    {
        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");
        $response->assertStatus(200);

        return collect($response->json('data'))
            ->firstWhere('type', 'work_order');
    }

    public function test_no_work_order_event_when_none_exists(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNull($event);
    }

    public function test_perbaikan_work_order_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterRepair::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'keluhan' => 'Air kecil',
            'tindakan_perbaikan' => 'Dibersihkan filter',
        ]);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('work_order', $event['type']);
        $this->assertEquals('perbaikan', $event['payload']['jenis']);
        $this->assertEquals('Air kecil', $event['payload']['details']['keluhan']);
        $this->assertEquals('Dibersihkan filter', $event['payload']['details']['tindakan_perbaikan']);
    }

    public function test_ganti_meter_work_order_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterReplacement::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'alasan_penggantian' => 'Meter rusak',
            'biaya_ganti_meter' => 150000,
        ]);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('ganti_meter', $event['payload']['jenis']);
        $this->assertEquals('Meter rusak', $event['payload']['details']['alasan_penggantian']);
    }

    public function test_tutup_work_order_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterClosed::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'alasan_tutup' => 'Permintaan pelanggan',
        ]);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('tutup', $event['payload']['jenis']);
        $this->assertEquals('Permintaan pelanggan', $event['payload']['details']['alasan_tutup']);
    }

    public function test_tera_meter_work_order_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterTera::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'keluhan' => 'Meter tidak akurat',
            'hasil_tera_meter' => 'Tidak layak pakai',
        ]);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('tera_meter', $event['payload']['jenis']);
        $this->assertEquals('Tidak layak pakai', $event['payload']['details']['hasil_tera_meter']);
    }

    public function test_cabut_work_order_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterDisconnection::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'alasan_cabut' => 'Pindah rumah',
        ]);

        $event = $this->getWorkOrderEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('cabut', $event['payload']['jenis']);
        $this->assertEquals('Pindah rumah', $event['payload']['details']['alasan_cabut']);
    }

    public function test_ubah_nama_confirmed_generates_confirmed_event(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterNameChange::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama_lama' => 'Budi',
            'nama_baru' => 'Budi Santoso',
            'alamat_lama' => 'Jl. A',
            'alamat_baru' => 'Jl. A',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");
        $response->assertStatus(200);

        $types = collect($response->json('data'))->pluck('type');
        $this->assertContains('work_order', $types);
        $this->assertContains('confirmed', $types);

        $confirmed = collect($response->json('data'))->firstWhere('type', 'confirmed');
        $this->assertEquals('ubah_nama', $confirmed['payload']['jenis']);
    }

    public function test_ubah_nama_not_confirmed_has_no_confirmed_event(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterNameChange::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama_lama' => 'Budi',
            'nama_baru' => 'Budi Santoso',
            'alamat_lama' => 'Jl. A',
            'alamat_baru' => 'Jl. A',
            'is_confirmed' => false,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");
        $response->assertStatus(200);

        $types = collect($response->json('data'))->pluck('type');
        $this->assertNotContains('confirmed', $types);
    }

    public function test_ganti_tarif_confirmed_generates_confirmed_event(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterRateChange::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test',
            'alasan_ganti_tarif' => 'Tambah daya',
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");
        $response->assertStatus(200);

        $confirmed = collect($response->json('data'))->firstWhere('type', 'confirmed');
        $this->assertNotNull($confirmed);
        $this->assertEquals('ganti_tarif', $confirmed['payload']['jenis']);
    }

    public function test_cannot_see_other_users_work_order_timeline(): void
    {
        $owner = $this->createCustomerUser();
        $other = $this->createCustomerUser(['email' => 'other@example.com']);
        $complaint = $this->createComplaint($owner);

        MeterReopening::factory()->create(['complaint_id' => $complaint->id, 'no_sambungan' => '01PNRG0001', 'nama' => 'A', 'alamat' => 'B', 'alasan_buka_kembali' => 'X', 'biaya_buka_kembali' => 0]);

        $this->actingAs($other)
            ->getJson("/api/v1/complaints/{$complaint->id}/timeline")
            ->assertStatus(404);
    }
}
