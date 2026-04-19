<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class ApiInfrastructureTest extends TestCase
{
    public function test_sanctum_config_exists(): void
    {
        $this->assertFileExists(config_path('sanctum.php'));

        $config = config('sanctum');
        $this->assertNotNull($config);
        $this->assertArrayHasKey('stateful', $config);
        $this->assertArrayHasKey('guard', $config);
        $this->assertEquals(['web'], $config['guard']);
    }

    public function test_permission_config_exists(): void
    {
        $this->assertFileExists(config_path('permission.php'));
        $this->assertNotNull(config('permission'));
    }

    public function test_api_log_channel_configured(): void
    {
        $channels = config('logging.channels');
        $this->assertArrayHasKey('api', $channels);
        $this->assertEquals('daily', $channels['api']['driver']);
        $this->assertStringContainsString('api.log', $channels['api']['path']);
    }

    public function test_api_v1_route_file_is_loaded(): void
    {
        $this->assertFileExists(base_path('routes/api_v1.php'));
    }

    public function test_ensure_customer_role_middleware_registered(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

        // Verify the middleware alias is resolvable by the router
        $router = app('router');
        $middleware = $router->getMiddleware();
        $this->assertArrayHasKey('customer', $middleware);
        $this->assertEquals(\App\Http\Middleware\EnsureCustomerRole::class, $middleware['customer']);
    }

    public function test_rate_limiters_are_defined(): void
    {
        $rateLimiter = app(\Illuminate\Cache\RateLimiter::class);

        $this->assertNotNull($rateLimiter->limiter('api'));
        $this->assertNotNull($rateLimiter->limiter('guest'));
        $this->assertNotNull($rateLimiter->limiter('login'));
        $this->assertNotNull($rateLimiter->limiter('upload'));
    }

    public function test_session_lifetime_configured_for_mobile(): void
    {
        // Session lifetime should be 43200 minutes (30 days) for mobile PWA
        $this->assertEquals(43200, (int) config('session.lifetime'));
    }

    public function test_user_model_has_roles_trait(): void
    {
        $user = new \App\Models\User;
        $this->assertTrue(
            method_exists($user, 'hasRole'),
            'User model should have hasRole method from HasRoles trait'
        );
    }

    public function test_user_model_implements_filament_user(): void
    {
        $this->assertTrue(
            is_subclass_of(\App\Models\User::class, \Filament\Models\Contracts\FilamentUser::class)
            || in_array(\Filament\Models\Contracts\FilamentUser::class, class_implements(\App\Models\User::class)),
            'User model should implement FilamentUser'
        );
    }

    public function test_migrations_exist_for_new_tables(): void
    {
        $migrations = glob(database_path('migrations/*create_customer_numbers_table*'));
        $this->assertNotEmpty($migrations, 'customer_numbers migration should exist');

        $migrations = glob(database_path('migrations/*create_uploads_table*'));
        $this->assertNotEmpty($migrations, 'uploads migration should exist');

        $migrations = glob(database_path('migrations/*add_user_id_to_customer_registrations*'));
        $this->assertNotEmpty($migrations, 'add user_id to customer_registrations migration should exist');

        $migrations = glob(database_path('migrations/*add_user_id_to_complaints*'));
        $this->assertNotEmpty($migrations, 'add user_id to complaints migration should exist');

        $migrations = glob(database_path('migrations/*add_source_to_customer_registrations*'));
        $this->assertNotEmpty($migrations, 'add source to customer_registrations migration should exist');
    }
}
