<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminAlert;
use Illuminate\Support\Facades\Notification;

/**
 * Single fan-out point for admin bell notifications.
 * Only admin accounts are notified; customer-originated writes and
 * verified webhook outcomes call in here, never probes or honeypots.
 */
class AdminNotifier
{
    public function alert(string $type, string $title, string $body, ?string $link = null): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminAlert($type, $title, $body, $link));
    }
}
