<?php

namespace Tests\Feature\Notification;

use App\Enums\WorkOrderEnum;
use App\Models\Complaint;
use App\Models\ComplaintFollowUp;
use App\Models\User;
use App\Notifications\ComplaintFollowUpCreated;
use App\Notifications\ComplaintStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComplaintObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_a_database_notification_when_complaint_status_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'status' => 'baru',
        ]);

        $complaint->update(['status' => 'in_progress']);

        Notification::assertSentTo(
            [$user],
            ComplaintStatusChanged::class,
            function (ComplaintStatusChanged $notification, array $channels) use ($complaint): bool {
                return $notification->status === 'in_progress'
                    && $channels === ['database']
                    && $complaint->fresh()->status === 'in_progress';
            }
        );
    }

    public function test_it_sends_a_database_notification_when_follow_up_is_created(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'status' => 'baru',
        ]);

        $followUp = ComplaintFollowUp::factory()->create([
            'complaint_id' => $complaint->id,
            'complaint_number' => $complaint->no_pengaduan,
            'work_order' => WorkOrderEnum::PERBAIKAN,
        ]);

        Notification::assertSentTo(
            [$user],
            ComplaintFollowUpCreated::class,
            function (ComplaintFollowUpCreated $notification, array $channels) use ($followUp): bool {
                return $notification->complaintFollowUp->is($followUp)
                    && $channels === ['database'];
            }
        );
    }
}
