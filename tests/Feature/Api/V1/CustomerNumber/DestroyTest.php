<?php

namespace Tests\Feature\Api\V1\CustomerNumber;

use App\Models\CustomerNumber;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class DestroyTest extends TestCase
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

    public function test_delete_own_number_succeeds(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        CustomerNumber::create([
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
            'verified_at' => now(),
        ]);

        $response = $this->deleteJson('/api/v1/customer-numbers/01PNRG0001');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Nomor sambungan berhasil dihapus dari akun Anda.',
            ]);

        $this->assertDatabaseMissing('customer_numbers', [
            'user_id' => $user->id,
            'no_sambungan' => '01PNRG0001',
        ]);
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

        $response = $this->deleteJson('/api/v1/customer-numbers/01PNRG0001');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);

        // Verify the other user's record was NOT deleted
        $this->assertDatabaseHas('customer_numbers', [
            'user_id' => $user2->id,
            'no_sambungan' => '01PNRG0001',
        ]);
    }

    public function test_non_existent_number_returns_404(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->deleteJson('/api/v1/customer-numbers/NONEXISTENT');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ]);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->deleteJson('/api/v1/customer-numbers/01PNRG0001');

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

        $response = $this->deleteJson('/api/v1/customer-numbers/01PNRG0001');

        $response->assertStatus(403);
    }
}
