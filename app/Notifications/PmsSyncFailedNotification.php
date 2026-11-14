<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PmsSyncFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $ticketNumber,
        private readonly string $provider,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('filament/notifications.pms_sync_failed.title', ['ticket' => $this->ticketNumber]),
            'body' => __('filament/notifications.pms_sync_failed.body', ['provider' => $this->provider]),
            'ticket_number' => $this->ticketNumber,
            'provider' => $this->provider,
        ];
    }
}
