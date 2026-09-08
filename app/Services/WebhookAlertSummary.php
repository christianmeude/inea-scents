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
     * @return array{rejectedCount: int, latestRejectedAt: ?string}
     */
    public function summarize(): array
    {
        $path = storage_path('logs/webhook.log');
        if (! is_readable($path)) {
            return ['rejectedCount' => 0, 'latestRejectedAt' => null];
        }

        $lines = $this->tail($path);
        $count = 0;
        $latest = null;
        $weekAgo = now()->subWeek();

        foreach ($lines as $line) {
            if (! str_contains($line, 'webhook rejected')) {
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
                $count++;
                if ($latest === null || $at->gt(\Carbon\Carbon::parse($latest))) {
                    $latest = $at->toDateTimeString();
                }
            }
        }

        return ['rejectedCount' => $count, 'latestRejectedAt' => $latest];
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
