<?php

namespace App\Notifications;

use App\Models\ComplaintFollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintFollowUpCreated extends Notification
{
    use Queueable;

    public function __construct(
        public ComplaintFollowUp $complaintFollowUp
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Pengaduan Ditindaklanjuti',
            'body' => 'Pengaduan Anda telah ditindaklanjuti dan sedang diproses.',
            'complaint_id' => $this->complaintFollowUp->complaint_id,
            'follow_up_id' => $this->complaintFollowUp->id,
            'work_order' => $this->complaintFollowUp->work_order?->value,
            'follow_up_at' => $this->complaintFollowUp->follow_up_at?->toIso8601String(),
        ];
    }
}
