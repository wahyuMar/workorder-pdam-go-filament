<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Notifications\ComplaintStatusChanged;

class ComplaintObserver
{
    public function updated(Complaint $complaint): void
    {
        if (! $complaint->wasChanged('status')) {
            return;
        }

        $complaint->user?->notify(new ComplaintStatusChanged($complaint->status));
    }
}
