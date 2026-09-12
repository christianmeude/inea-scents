<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminAlert extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $link = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'link' => $this->link,
        ];
    }
}
