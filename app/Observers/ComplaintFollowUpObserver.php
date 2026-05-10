<?php

namespace App\Observers;

use App\Models\ComplaintFollowUp;
use App\Notifications\ComplaintFollowUpCreated;

class ComplaintFollowUpObserver
{
    public function created(ComplaintFollowUp $complaintFollowUp): void
    {
        $complaintFollowUp->complaint?->user?->notify(new ComplaintFollowUpCreated($complaintFollowUp));
    }
}
