<?php

namespace Tests\Feature\Admin;

use App\Services\WebhookAlertSummary;
use Tests\TestCase;

class WebhookHygieneTest extends TestCase
{
    public function test_summary_skips_testing_env_lines()
    {
        $path = tempnam(sys_get_temp_dir(), 'webhook') . '.log';
        file_put_contents($path, implode("\n", [
            '[2026-09-12 10:00:00] testing.WARNING: PayMongo webhook rejected: invalid signature {"ip":"127.0.0.1"}',
            '[2026-09-12 10:01:00] production.WARNING: PayMongo webhook rejected: invalid signature {"ip":"203.0.113.9"}',
            '[2026-09-12 10:02:00] production.INFO: PayMongo webhook ignored: unknown type {"event_id":"evt_1"}',
            '[2026-09-12 10:03:00] production.WARNING: PayMongo webhook no-match: paid event for unknown or non-pending booking',
        ]));

        try {
            $summary = (new WebhookAlertSummary(path: $path))->summarize();
        } finally {
            @unlink($path);
        }

        $this->assertSame(1, $summary['rejectedCount']);
        $this->assertSame(1, $summary['ignoredCount']);
        $this->assertSame(1, $summary['unmatchedCount']);
        $this->assertSame('2026-09-12 10:01:00', $summary['latestRejectedAt']);
    }

    public function test_webhook_route_is_throttled()
    {
        $route = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
            ->first(fn ($r) => $r->uri() === 'api/webhooks/paymongo');

        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->gatherMiddleware());
    }
}
