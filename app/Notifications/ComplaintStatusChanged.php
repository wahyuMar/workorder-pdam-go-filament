<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ComplaintStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $message = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Status Pengaduan Diperbarui',
            'body' => $this->message ?? "Status pengaduan Anda telah berubah menjadi: {$this->status}",
            'status' => $this->status,
        ];
    }
}
