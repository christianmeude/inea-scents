<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class LandingCorsTest extends TestCase
{
    public function test_cors_config_merges_landing_url(): void
    {
        foreach (['FRONTEND_URL' => 'https://client.test', 'LANDING_URL' => 'https://landing.test'] as $k => $v) {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }

        try {
            $config = require config_path('cors.php');
        } finally {
            foreach (['FRONTEND_URL', 'LANDING_URL'] as $k) {
                putenv($k);
                unset($_ENV[$k], $_SERVER[$k]);
            }
        }

        $this->assertContains('https://client.test', $config['allowed_origins']);
        $this->assertContains('https://landing.test', $config['allowed_origins']);
        $this->assertNotContains('', $config['allowed_origins']);
    }

    public function test_preflight_from_landing_origin_succeeds(): void
    {
        config(['cors.allowed_origins' => ['https://landing.test']]);

        $response = $this->call(
            'OPTIONS',
            '/api/inquiries',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'https://landing.test',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ]
        );

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://landing.test');
    }

    public function test_preflight_from_unknown_origin_gets_no_allow_header(): void
    {
        config(['cors.allowed_origins' => ['https://landing.test']]);

        $response = $this->call(
            'OPTIONS',
            '/api/inquiries',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'https://evil.test',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            ]
        );

        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }
}
