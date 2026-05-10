<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\MeterClosed;
use App\Models\MeterDisconnection;
use App\Models\MeterRepair;
use App\Models\MeterReplacement;
use App\Models\MeterReplacementHandover;
use App\Models\MeterReopening;
use App\Models\MeterTera;
use App\Models\RepairReport;
use App\Models\SubscriptionCancellation;
use App\Models\SubscriptionClosure;
use App\Models\SubscriptionReopening;
use App\Models\TeraMeterReport;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class BeritaAcaraTest extends TestCase
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

    private function getBeritaAcaraEvent(User $user, Complaint $complaint): ?array
    {
        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");
        $response->assertStatus(200);

        return collect($response->json('data'))
            ->firstWhere('type', 'berita_acara');
    }

    public function test_no_berita_acara_event_when_none_exists(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNull($event);
    }

    public function test_perbaikan_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        RepairReport::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'lokasi' => 'Depan rumah',
            'catatan' => 'Selesai diperbaiki',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('berita_acara', $event['type']);
        $this->assertArrayHasKey('type', $event);
        $this->assertArrayHasKey('at', $event);
        $this->assertArrayHasKey('payload', $event);
        $this->assertEquals('perbaikan', $event['payload']['jenis']);
        $this->assertEquals('Selesai diperbaiki', $event['payload']['details']['catatan']);
        $this->assertEquals('Depan rumah', $event['payload']['lokasi']);
    }

    public function test_tera_meter_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        TeraMeterReport::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'catatan' => 'Meter tidak layak',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('tera_meter', $event['payload']['jenis']);
        $this->assertEquals('Meter tidak layak', $event['payload']['details']['catatan']);
    }

    public function test_ganti_meter_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        MeterReplacementHandover::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'merk_wm_lama' => 'Sensus',
            'no_wm_lama' => 'SN001',
            'merk_wm_baru' => 'Itron',
            'no_wm_baru' => 'IT002',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('ganti_meter', $event['payload']['jenis']);
        $this->assertEquals('Itron', $event['payload']['details']['merk_wm_baru']);
        $this->assertEquals('IT002', $event['payload']['details']['no_wm_baru']);
    }

    public function test_cabut_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        SubscriptionCancellation::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'catatan' => 'Pelanggan pindah',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('cabut', $event['payload']['jenis']);
        $this->assertEquals('Pelanggan pindah', $event['payload']['details']['catatan']);
    }

    public function test_tutup_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        SubscriptionClosure::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'catatan' => 'Tutup sementara',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('tutup', $event['payload']['jenis']);
        $this->assertEquals('Tutup sementara', $event['payload']['details']['catatan']);
    }

    public function test_buka_kembali_berita_acara_appears_in_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        SubscriptionReopening::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'catatan' => 'Buka kembali atas permintaan pelanggan',
        ]);

        $event = $this->getBeritaAcaraEvent($user, $complaint);

        $this->assertNotNull($event);
        $this->assertEquals('buka_kembali', $event['payload']['jenis']);
        $this->assertEquals('Buka kembali atas permintaan pelanggan', $event['payload']['details']['catatan']);
    }

    public static function beritaAcaraConfirmationProvider(): array
    {
        return [
            'perbaikan' => [MeterRepair::class, RepairReport::class],
            'tera meter' => [MeterTera::class, TeraMeterReport::class],
            'ganti meter' => [MeterReplacement::class, MeterReplacementHandover::class],
            'cabut langganan' => [MeterDisconnection::class, SubscriptionCancellation::class],
            'tutup langganan' => [MeterClosed::class, SubscriptionClosure::class],
            'buka kembali' => [MeterReopening::class, SubscriptionReopening::class],
        ];
    }

    /**
     * @dataProvider beritaAcaraConfirmationProvider
     */
    public function test_creating_berita_acara_confirms_related_work_order(string $workOrderModel, string $beritaAcaraModel): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $workOrder = $workOrderModel::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
            'is_confirmed' => false,
        ]);

        $beritaAcaraModel::factory()->create([
            'complaint_id' => $complaint->id,
            'no_sambungan' => $complaint->no_sambungan,
            'nama' => $complaint->nama,
            'alamat' => 'Jl. Test No. 1',
        ]);

        $this->assertTrue($workOrder->fresh()->is_confirmed);
    }

    public function test_cannot_see_other_users_berita_acara_timeline(): void
    {
        $owner = $this->createCustomerUser();
        $other = $this->createCustomerUser(['email' => 'other@example.com']);
        $complaint = $this->createComplaint($owner);

        RepairReport::factory()->create(['complaint_id' => $complaint->id, 'no_sambungan' => '01PNRG0001', 'nama' => 'A', 'alamat' => 'B']);

        $this->actingAs($other)
            ->getJson("/api/v1/complaints/{$complaint->id}/timeline")
            ->assertStatus(404);
    }
}
