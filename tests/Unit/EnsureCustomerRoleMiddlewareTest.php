<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureCustomerRole;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsureCustomerRoleMiddlewareTest extends TestCase
{
    public function test_rejects_unauthenticated_request(): void
    {
        $middleware = new EnsureCustomerRole;
        $request = Request::create('/api/v1/test', 'GET');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized', $response->getContent());
    }

    public function test_rejects_user_without_customer_role(): void
    {
        $middleware = new EnsureCustomerRole;
        $request = Request::create('/api/v1/test', 'GET');

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRole'])
            ->getMock();
        $user->method('hasRole')->with('customer')->willReturn(false);
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_allows_user_with_customer_role(): void
    {
        $middleware = new EnsureCustomerRole;
        $request = Request::create('/api/v1/test', 'GET');

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRole'])
            ->getMock();
        $user->method('hasRole')->with('customer')->willReturn(true);
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
