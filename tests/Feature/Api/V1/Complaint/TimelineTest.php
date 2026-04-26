<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintFollowUp;
use App\Models\ComplaintType;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabaseCompat;

    private ComplaintType $complaintType;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
        $this->complaintType = ComplaintType::create(['name' => 'Kebocoran Pipa', 'is_active' => true]);
    }

    private function createCustomerUser(array $overrides = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ], $overrides));

        $user->assignRole('customer');

        return $user;
    }

    private function createComplaint(User $user, array $overrides = []): Complaint
    {
        $complaint = Complaint::create(array_merge([
            'user_id' => $user->id,
            'complaint_type_id' => $this->complaintType->id,
            'no_sambungan' => '01PNRG0001',
            'nama' => 'Test Customer',
            'judul_pengaduan' => 'Air Tidak Mengalir',
            'isi_pengaduan' => 'Sudah 2 hari air tidak mengalir.',
            'sumber' => 'mobile_apps',
            'tanggal' => now(),
        ], $overrides));

        $complaint->refresh();

        return $complaint;
    }

    public function test_can_view_complaint_timeline(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        ComplaintFollowUp::create([
            'complaint_id' => $complaint->id,
            'complaint_number' => $complaint->no_pengaduan,
            'work_order' => 'Ganti Meter',
            'notes' => 'Teknisi ditugaskan.',
            'follow_up_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['type', 'at', 'payload'],
                ],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'complaint_created')
            ->assertJsonPath('data.1.type', 'follow_up')
            ->assertJsonPath('data.1.payload.work_order', 'Ganti Meter')
            ->assertJsonPath('data.1.payload.notes', 'Teknisi ditugaskan.');
    }

    public function test_timeline_ordered_chronologically(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $older = ComplaintFollowUp::create([
            'complaint_id' => $complaint->id,
            'complaint_number' => $complaint->no_pengaduan,
            'work_order' => 'Ganti Meter',
            'notes' => 'First follow-up',
            'follow_up_at' => now()->addDay(),
        ]);

        $newer = ComplaintFollowUp::create([
            'complaint_id' => $complaint->id,
            'complaint_number' => $complaint->no_pengaduan,
            'work_order' => 'Tutup',
            'notes' => 'Second follow-up',
            'follow_up_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $this->assertEquals('complaint_created', $response->json('data.0.type'));
        $this->assertEquals($older->id, $response->json('data.1.payload.id'));
        $this->assertEquals($newer->id, $response->json('data.2.payload.id'));
    }

    public function test_empty_timeline_returns_empty_data(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'complaint_created');
    }

    public function test_cannot_view_other_users_complaint_timeline(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser(['email' => 'other@example.com', 'name' => 'Other User']);

        $complaint = $this->createComplaint($userB);

        $response = $this->actingAs($userA)->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(404);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $response = $this->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $customerUser = $this->createCustomerUser(['email' => 'customer@example.com']);
        $complaint = $this->createComplaint($customerUser);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}/timeline");

        $response->assertStatus(403);
    }
}
