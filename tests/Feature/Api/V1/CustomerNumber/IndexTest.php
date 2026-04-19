<?php

namespace Tests\Feature\Api\V1\CustomerNumber;

use App\Models\CustomerNumber;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_authenticated_customer_gets_own_numbers(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);
        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0002',
            'verified_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/customer-numbers');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'no_sambungan', 'verified_at', 'created_at'],
                ],
            ]);
    }

    public function test_only_returns_own_numbers_not_others(): void
    {
        $user1 = $this->createCustomerUser();
        $user2 = $this->createCustomerUser([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        CustomerNumber::create([
            'user_id' => $user1->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);
        CustomerNumber::create([
            'user_id' => $user2->id,
            'no_sambungan' => '01PNRG0002',
            'verified_at' => now(),
        ]);

        $this->actingAs($user1);
        $response = $this->getJson('/api/v1/customer-numbers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_sambungan', '01PNRG0001');
    }

    public function test_customer_with_no_numbers_returns_empty_array(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->getJson('/api/v1/customer-numbers');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson(['data' => []]);
    }

    public function test_response_uses_correct_resource_format(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $number = CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/customer-numbers');

        $response->assertStatus(200);

        $item = $response->json('data.0');
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('no_sambungan', $item);
        $this->assertArrayHasKey('verified_at', $item);
        $this->assertArrayHasKey('created_at', $item);
        $this->assertArrayNotHasKey('user_id', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/customer-numbers');

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

        $response = $this->getJson('/api/v1/customer-numbers');

        $response->assertStatus(403);
    }
}
