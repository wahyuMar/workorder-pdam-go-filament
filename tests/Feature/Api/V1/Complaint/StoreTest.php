<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Exceptions\BillingApiException;
use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\CustomerNumber;
use App\Models\User;
use App\Services\CustomerLookupService;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class StoreTest extends TestCase
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

    private function createVerifiedNumber(User $user, string $noSambungan = '01PNRG0001'): CustomerNumber
    {
        return CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => $noSambungan,
            'verified_at' => now(),
        ]);
    }

    private function fakeBillingData(array $overrides = []): array
    {
        return array_merge([
            'nama_pelanggan' => 'Pak Joko',
            'alamat_pelanggan' => 'Jl. Merdeka No. 5',
            'no_ktp' => '3275123456780003',
        ], $overrides);
    }

    private function mockBillingSuccess(string $noSambungan = '01PNRG0001', array $billingOverrides = []): void
    {
        $this->mock(CustomerLookupService::class, function (MockInterface $mock) use ($noSambungan, $billingOverrides) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with($noSambungan, true)
                ->once()
                ->andReturn(['data' => $this->fakeBillingData($billingOverrides), 'message' => null]);
        });
    }

    private function mockBillingFailure(): void
    {
        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->once()
                ->andThrow(new BillingApiException('Connection refused'));
        });
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'no_sambungan' => '01PNRG0001',
            'complaint_type_id' => $this->complaintType->id,
            'judul_pengaduan' => 'Air Tidak Mengalir',
            'isi_pengaduan' => 'Sudah 2 hari air tidak mengalir di rumah saya.',
        ], $overrides);
    }

    public function test_can_submit_complaint_for_verified_number(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'no_pengaduan',
                    'complaint_type',
                    'no_sambungan',
                    'nama',
                    'alamat',
                    'latitude',
                    'longitude',
                    'judul_pengaduan',
                    'isi_pengaduan',
                    'sumber',
                    'status',
                    'priority',
                    'tanggal',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('complaints', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'judul_pengaduan' => 'Air Tidak Mengalir',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);

        $this->assertEquals(-6.917464, $response->json('data.latitude'));
        $this->assertEquals(107.619123, $response->json('data.longitude'));
        $this->assertStringStartsWith('PGD-', $response->json('data.no_pengaduan'));
    }

    public function test_can_submit_complaint_with_photos(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $photos = ['uploads/photo1.jpg', 'uploads/photo2.jpg', 'uploads/photo3.jpg'];

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'foto' => $photos,
        ]));

        $response->assertStatus(201);
        $this->assertEquals($photos, $response->json('data.foto'));

        $complaint = Complaint::first();
        $this->assertEquals($photos, $complaint->foto);
    }

    public function test_cannot_submit_for_unverified_number(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        // Create unverified number (no verified_at)
        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors('no_sambungan');
    }

    public function test_cannot_submit_for_another_users_number(): void
    {
        $user = $this->createCustomerUser();
        $otherUser = $this->createCustomerUser(['email' => 'other@example.com']);
        $this->actingAs($user);

        // Number belongs to other user
        $this->createVerifiedNumber($otherUser);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors('no_sambungan');
    }

    public function test_can_submit_complaint_with_exactly_5_photos(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $photos = ['a.jpg', 'b.jpg', 'c.jpg', 'd.jpg', 'e.jpg'];

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'foto' => $photos,
        ]));

        $response->assertStatus(201);
        $this->assertEquals($photos, $response->json('data.foto'));
    }

    public function test_cannot_submit_with_more_than_5_photos(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);

        $photos = ['a.jpg', 'b.jpg', 'c.jpg', 'd.jpg', 'e.jpg', 'f.jpg'];

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'foto' => $photos,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('foto');
    }

    public function test_auto_fills_nama_alamat_from_billing(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess('01PNRG0001', [
            'nama_pelanggan' => 'Budi Setiawan',
            'alamat_pelanggan' => 'Jl. Sudirman No. 10',
        ]);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(201);
        $this->assertEquals('Budi Setiawan', $response->json('data.nama'));
        $this->assertEquals('Jl. Sudirman No. 10', $response->json('data.alamat'));

        $this->assertDatabaseHas('complaints', [
            'nama' => 'Budi Setiawan',
            'alamat' => 'Jl. Sudirman No. 10',
        ]);
    }

    public function test_auto_sets_sumber_status_priority(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(201);
        $this->assertEquals('mobile_apps', $response->json('data.sumber'));
        $this->assertEquals('pending', $response->json('data.status'));
        $this->assertEquals('medium', $response->json('data.priority'));

        $this->assertDatabaseHas('complaints', [
            'sumber' => 'mobile_apps',
            'status' => 'pending',
            'priority' => 'medium',
        ]);
    }

    public function test_auto_generates_no_pengaduan(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(201);

        $noPengaduan = $response->json('data.no_pengaduan');
        $this->assertMatchesRegularExpression('/^PGD-\d{8}-\d{4}$/', $noPengaduan);
    }

    public function test_sets_user_id_from_auth(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess();

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(201);

        $complaint = Complaint::first();
        $this->assertEquals($user->id, $complaint->user_id);

        // user_id should NOT be in response
        $this->assertArrayNotHasKey('user_id', $response->json('data'));
    }

    public function test_masks_no_ktp_in_response(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingSuccess('01PNRG0001', [
            'no_ktp' => '3275012345670003',
        ]);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(201);

        // no_ktp from billing is not stored in complaint (not in validated data)
        // but if present in model, it should be masked
        // The billing no_ktp is NOT auto-filled to complaint (only nama/alamat are)
        $this->assertNull($response->json('data.no_ktp'));
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);

        $response = $this->postJson('/api/v1/complaints', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'no_sambungan',
                'complaint_type_id',
                'judul_pengaduan',
                'isi_pengaduan',
            ]);
    }

    public function test_invalid_complaint_type_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'complaint_type_id' => 99999,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('complaint_type_id');
    }

    public function test_inactive_complaint_type_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);

        $inactiveType = ComplaintType::create(['name' => 'Inactive', 'is_active' => false]);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload([
            'complaint_type_id' => $inactiveType->id,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('complaint_type_id');
    }

    public function test_billing_api_failure_returns_503(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);
        $this->createVerifiedNumber($user);
        $this->mockBillingFailure();

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(503)
            ->assertJson(['message' => 'Layanan billing sedang tidak tersedia.']);

        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        // No customer role assigned
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/complaints', $this->validPayload());

        $response->assertStatus(403);
    }
}
