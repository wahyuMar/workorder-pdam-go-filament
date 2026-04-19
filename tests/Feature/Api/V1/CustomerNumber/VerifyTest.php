<?php

namespace Tests\Feature\Api\V1\CustomerNumber;

use App\Exceptions\BillingApiException;
use App\Models\User;
use App\Services\CustomerLookupService;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class VerifyTest extends TestCase
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

    public function test_successful_verify_returns_200_with_billing_data(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => $this->fakeBillingData(), 'message' => null]);
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['no_sambungan', 'nama_pelanggan', 'alamat_pelanggan', 'no_ktp'],
            ])
            ->assertJson([
                'data' => [
                    'no_sambungan' => '01PNRG0001',
                    'nama_pelanggan' => 'Pak Joko',
                    'alamat_pelanggan' => 'Jl. Merdeka No. 5',
                ],
            ]);

        // F5: Verify extra billing fields are NOT exposed in response
        $responseData = $response->json('data');
        $this->assertArrayNotHasKey('hp_pelanggan', $responseData);
        $this->assertArrayNotHasKey('email', $responseData);
        $this->assertArrayNotHasKey('no_telp_pelanggan', $responseData);
        $this->assertArrayNotHasKey('latitude', $responseData);
        $this->assertArrayNotHasKey('longitude', $responseData);
        $this->assertArrayNotHasKey('id_pelanggan', $responseData);
    }

    public function test_nik_is_masked_in_response(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => $this->fakeBillingData(['no_ktp' => '3275123456780003']), 'message' => null]);
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'no_ktp' => '3275****0003',
                ],
            ]);

        $this->assertStringNotContainsString('3275123456780003', $response->getContent());
    }

    public function test_non_existent_nomor_returns_404(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('INVALID999', true)
                ->once()
                ->andReturn(['data' => null, 'message' => 'Customer not found']);
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => 'INVALID999',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);
    }

    public function test_billing_api_failure_returns_503(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andThrow(new BillingApiException('Connection timeout'));
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ])
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('file')
            ->assertJsonMissingPath('line');
    }

    public function test_missing_no_sambungan_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/customer-numbers/verify', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_sambungan']);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
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

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(403);
    }

    public function test_null_nik_returns_null_in_response(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => $this->fakeBillingData(['no_ktp' => null]), 'message' => null]);
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'no_ktp' => null,
                ],
            ]);
    }

    public function test_short_nik_is_fully_masked(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => $this->fakeBillingData(['no_ktp' => '12345678']), 'message' => null]);
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'no_ktp' => '****',
                ],
            ]);

        $this->assertStringNotContainsString('12345678', $response->getContent());
    }

    public function test_billing_http_5xx_returns_503(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andThrow(new BillingApiException('Billing API returned HTTP 500'));
        });

        $response = $this->postJson('/api/v1/customer-numbers/verify', [
            'no_sambungan' => '01PNRG0001',
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ]);
    }
}
