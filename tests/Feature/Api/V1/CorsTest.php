<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_preflight_request_allows_frontend_origin_with_credentials(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type,x-xsrf-token',
        ])->options('/api/v1/auth/register');

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }
}
