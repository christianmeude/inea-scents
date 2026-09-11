<?php

namespace App\Services;

/**
 * Read-only summary of the dedicated webhook log for the admin dashboard.
 * The reject path never persists anything (not even here) — this only
 * reads what the logger already wrote, so floods stay visible to Admin.
 */
class WebhookAlertSummary
{
    public function __construct(private int $tailLines = 500) {}

    /**
     * @return array{rejectedCount: int, latestRejectedAt: ?string, ignoredCount: int, latestIgnoredAt: ?string, unmatchedCount: int, latestUnmatchedAt: ?string}
     */
    public function summarize(): array
    {
        $empty = [
            'rejectedCount' => 0, 'latestRejectedAt' => null,
            'ignoredCount' => 0, 'latestIgnoredAt' => null,
            'unmatchedCount' => 0, 'latestUnmatchedAt' => null,
        ];

        $path = storage_path('logs/webhook.log');
        if (! is_readable($path)) {
            return $empty;
        }

        $markers = [
            'webhook rejected' => ['rejectedCount', 'latestRejectedAt'],
            'webhook ignored' => ['ignoredCount', 'latestIgnoredAt'],
            'webhook no-match' => ['unmatchedCount', 'latestUnmatchedAt'],
        ];

        $lines = $this->tail($path);
        $weekAgo = now()->subWeek();

        foreach ($lines as $line) {
            foreach ($markers as $marker => [$countKey, $latestKey]) {
                if (! str_contains($line, $marker)) {
                    continue;
                }
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $m)) {
                    try {
                        $at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $m[1]);
                    } catch (\Throwable) {
                        continue;
                    }
                    if ($at->lt($weekAgo)) {
                        continue;
                    }
                    $empty[$countKey]++;
                    if ($empty[$latestKey] === null || $at->gt(\Carbon\Carbon::parse($empty[$latestKey]))) {
                        $empty[$latestKey] = $at->toDateTimeString();
                    }
                }
            }
        }

        return $empty;
    }

    private function tail(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        fseek($handle, 0, SEEK_END);
        $pos = ftell($handle);
        $chunk = '';
        $lines = [];
        $size = 4096;

        while ($pos > 0 && count($lines) <= $this->tailLines) {
            $read = min($size, $pos);
            $pos -= $read;
            fseek($handle, $pos);
            $chunk = fread($handle, $read) . $chunk;
            $lines = explode("\n", $chunk);
        }

        fclose($handle);

        return array_slice($lines, -$this->tailLines);
    }
}
