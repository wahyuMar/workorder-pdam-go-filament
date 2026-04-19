<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabaseCompat;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
    }

    private function createCustomerUser(): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user->assignRole('customer');

        return $user;
    }

    public function test_successful_logout_returns_200(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);
    }

    public function test_user_is_guest_after_logout(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $this->postJson('/api/v1/auth/logout');

        $this->assertGuest('web');
    }

    public function test_unauthenticated_logout_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_csrf_cookie_endpoint_works(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertStatus(204)
            ->assertCookie('XSRF-TOKEN');
    }
}
