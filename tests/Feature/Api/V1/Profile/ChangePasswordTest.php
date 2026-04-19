<?php

namespace Tests\Feature\Api\V1\Profile;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
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

    public function test_change_password_with_correct_current_password(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully.',
            ]);
    }

    public function test_can_login_with_new_password_after_change(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertStatus(200);

        // Verify password was actually changed in database
        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $user->password));
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('password123', $user->password));
    }

    public function test_wrong_current_password_returns_422(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_new_password_too_short_returns_422(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_confirmation_mismatch_returns_422(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $user = $this->createCustomerUser();

        $this->actingAs($user);

        $response = $this->putJson('/api/v1/profile/password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
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

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(403);
    }
}
