<?php

namespace Tests\Feature\Api\V1\Profile;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
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

    public function test_update_name_only(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => 'test@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_email_only(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'email' => 'newemail@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'newemail@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_update_both_name_and_email(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ]);
    }

    public function test_duplicate_email_returns_422(): void
    {
        $this->createCustomerUser(['email' => 'existing@example.com']);

        $user = $this->createCustomerUser([
            'name' => 'Another User',
            'email' => 'another@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_keeping_own_email_succeeds(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'email' => 'test@example.com',
                ],
            ]);
    }

    public function test_invalid_email_format_returns_422(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_empty_payload_returns_200(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile', []);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->putJson('/api/v1/profile', [
            'name' => 'New Name',
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

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_change_password_via_profile_update(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $this->putJson('/api/v1/profile', [
            'name' => 'Updated',
            'password' => 'hackedpassword',
        ])->assertStatus(200);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $user->password));
    }
}
