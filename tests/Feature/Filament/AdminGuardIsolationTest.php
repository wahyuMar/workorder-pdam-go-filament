<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class AdminGuardIsolationTest extends TestCase
{
    use RefreshDatabaseCompat;

    public function test_customer_web_session_and_admin_panel_session_can_coexist(): void
    {
        Role::findOrCreate('customer', 'web');

        /** @var User $customer */
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
        ]);
        $customer->assignRole('customer');

        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($customer, 'web');
        $this->actingAs($admin, 'admin');

        $this->assertAuthenticated('web');
        $this->assertAuthenticated('admin');
        $this->assertSame($customer->id, auth()->guard('web')->id());
        $this->assertSame($admin->id, auth()->guard('admin')->id());

        $this->get('/admin')->assertOk();
        $this->getJson('/api/v1/profile')->assertOk();
    }
}
