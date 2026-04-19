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

class BillingTest extends TestCase
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

    private function mockBillingService(array $returnData): void
    {
        $this->mock(CustomerLookupService::class, function (MockInterface $mock) use ($returnData) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => $returnData, 'message' => null]);
        });
    }

    public function test_verified_number_returns_billing_data(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->mockBillingService($this->fakeBillingData());

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

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
    }

    public function test_nik_is_masked_in_billing_response(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->mockBillingService($this->fakeBillingData(['no_ktp' => '3275123456780003']));

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'no_ktp' => '3275****0003',
                ],
            ]);

        $this->assertStringNotContainsString('3275123456780003', $response->getContent());
    }

    public function test_non_owned_number_returns_404(): void
    {
        $user1 = $this->createCustomerUser();
        $user2 = $this->createCustomerUser([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        CustomerNumber::create([
            'user_id' => $user2->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->actingAs($user1);

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);
    }

    public function test_non_existent_number_returns_404(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/customer-numbers/NONEXISTENT/billing');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);
    }

    public function test_billing_api_failure_returns_503(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andThrow(new BillingApiException('Connection timeout'));
        });

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

        $response->assertStatus(503)
            ->assertJson([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ])
            ->assertJsonMissingPath('exception')
            ->assertJsonMissingPath('trace');
    }

    public function test_billing_returns_null_data_returns_404(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $this->mock(CustomerLookupService::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetchByNoSambungan')
                ->with('01PNRG0001', true)
                ->once()
                ->andReturn(['data' => null, 'message' => 'Not found']);
        });

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Data billing tidak ditemukan.',
            ]);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

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

        $response = $this->getJson('/api/v1/customer-numbers/01PNRG0001/billing');

        $response->assertStatus(403);
    }
}
