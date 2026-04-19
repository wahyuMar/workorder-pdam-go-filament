<?php

namespace Tests\Feature\Api\V1\CustomerNumber;

use App\Exceptions\BillingApiException;
use App\Models\CustomerNumber;
use App\Models\User;
use App\Services\CustomerLookupService;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class ConfirmTest extends TestCase
{
    use RefreshDatabaseCompat;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
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

    private function fakeBillingData(array $overrides = []): array
    {
        return array_merge([
            'id_pelanggan' => 123,
            'no_sambungan' => '01PNRG0001',
            'nama_pelanggan' => 'Pak Joko',
            'alamat_pelanggan' => 'Jl. Merdeka No. 5',
            'no_ktp' => '3275123456780003',
            'hp_pelanggan' => '081234567890',
            'no_telp_pelanggan' => '021555123',
            'email' => 'joko@example.com',
            'latitude' => '-6.2088',
            'longitude' => '106.8456',
        ], $overrides);
    }

    private function mockBillingService(array $billingData, string $noSambungan = '01PNRG0001'): void
    {
        $this->mock(CustomerLookupService::class, function (MockInterface $mock) use ($billingData, $noSambungan) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with($noSambungan, true)
                ->once()
                ->andReturn(['data' => $billingData, 'message' => null]);
        });
    }

    public function test_matching_nik_creates_customer_number_and_returns_200(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mockBillingService($this->fakeBillingData());

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);

        $record = CustomerNumber::where('user_id', $user->id)->first();
        $this->assertNotNull($record->verified_at);
    }

    public function test_response_uses_customer_number_resource_format(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mockBillingService($this->fakeBillingData());

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'no_sambungan', 'verified_at', 'created_at'],
            ])
            ->assertJson([
                'data' => [
                    'no_sambungan' => '01PNRG0001',
                ],
            ]);

        // NIK must never appear in response
        $responseData = $response->json('data');
        $this->assertArrayNotHasKey('no_ktp', $responseData);
        $this->assertArrayNotHasKey('nik', $responseData);
    }

    public function test_nik_mismatch_returns_422_and_no_record_created(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mockBillingService($this->fakeBillingData(['no_ktp' => '3275123456780003']));

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '9999999999999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik'])
            ->assertJson([
                'message' => 'NIK tidak sesuai dengan data pelanggan.',
                'errors' => [
                    'nik' => ['NIK tidak sesuai dengan data pelanggan.'],
                ],
            ]);

        // Must NOT reveal actual billing NIK in error
        $this->assertStringNotContainsString('3275123456780003', $response->getContent());

        $this->assertDatabaseMissing('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);
    }

    public function test_already_linked_returns_409(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        // Create existing record
        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->mockBillingService($this->fakeBillingData());

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Nomor sambungan sudah terhubung ke akun Anda.',
            ]);
    }

    public function test_billing_api_failure_returns_503_and_no_record_created(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andThrow(new BillingApiException('Connection timeout'));
        });

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ])
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('file')
            ->assertJsonMissingPath('line');

        $this->assertDatabaseMissing('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);
    }

    public function test_nomor_not_found_in_billing_returns_404(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('NOTEXIST01', true)
                ->once()
                ->andReturn(['data' => null, 'message' => 'Customer not found']);
        });

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => 'NOTEXIST01',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(403);
    }

    public function test_missing_fields_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/customer-numbers/confirm', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_sambungan', 'nik']);
    }

    public function test_nik_never_exposed_in_any_success_response(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $billingNik = '3275123456780003';
        $this->mockBillingService($this->fakeBillingData(['no_ktp' => $billingNik]));

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => $billingNik,
        ]);

        $response->assertStatus(200);

        // Full NIK must never appear in response body
        $this->assertStringNotContainsString($billingNik, $response->getContent());
    }

    public function test_billing_has_no_nik_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mockBillingService($this->fakeBillingData(['no_ktp' => null]));

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Data NIK pelanggan belum tersedia di sistem billing.',
            ]);

        $this->assertDatabaseMissing('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);
    }

    public function test_nik_must_be_digits_only(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => 'ABCDEF1234567890',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_retry_after_mismatch_succeeds(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        // Mock handles both calls — first with wrong NIK, second with correct
        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->twice()
                ->andReturn(['data' => $this->fakeBillingData(), 'message' => null]);
        });

        // First attempt: wrong NIK → 422
        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '9999888877776666',
        ]);
        $response->assertStatus(422);

        // Second attempt: correct NIK → 200 (no lockout)
        $response = $this->postJson('/api/v1/customer-numbers/confirm', [
            'no_sambungan' => '01PNRG0001',
            'nik' => '3275123456780003',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);
    }
}
