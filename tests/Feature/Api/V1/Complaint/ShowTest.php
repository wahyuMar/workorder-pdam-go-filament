<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class ShowTest extends TestCase
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
            'no_ktp' => '3275012345670003',
            'judul_pengaduan' => 'Air Tidak Mengalir',
            'isi_pengaduan' => 'Sudah 2 hari air tidak mengalir.',
            'sumber' => 'mobile_apps',
            'tanggal' => now(),
        ], $overrides));

        $complaint->refresh();

        return $complaint;
    }

    public function test_can_view_own_complaint(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user, [
            'judul_pengaduan' => 'Pipa Bocor',
            'isi_pengaduan' => 'Pipa di depan rumah bocor.',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'no_pengaduan',
                    'complaint_type',
                    'no_sambungan',
                    'nama',
                    'alamat',
                    'latitude',
                    'longitude',
                    'email',
                    'no_hp',
                    'no_ktp',
                    'sumber',
                    'judul_pengaduan',
                    'isi_pengaduan',
                    'foto',
                    'tanggal',
                    'status',
                    'priority',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.id', $complaint->id)
            ->assertJsonPath('data.judul_pengaduan', 'Pipa Bocor');
    }

    public function test_cannot_view_other_users_complaint(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser(['email' => 'other@example.com', 'name' => 'Other User']);

        $complaint = $this->createComplaint($userB);

        $response = $this->actingAs($userA)->getJson("/api/v1/complaints/{$complaint->id}");

        $response->assertStatus(404);
    }

    public function test_nonexistent_complaint_returns_404(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/complaints/99999');

        $response->assertStatus(404);
    }

    public function test_masks_no_ktp_in_response(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user, ['no_ktp' => '3275012345670003']);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}");

        $response->assertStatus(200);
        $this->assertEquals('3275****0003', $response->json('data.no_ktp'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'no_ktp' => '3275012345670003',
        ]);
    }

    public function test_includes_complaint_type_detail(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}");

        $response->assertStatus(200);
        $this->assertEquals($this->complaintType->id, $response->json('data.complaint_type.id'));
        $this->assertEquals('Kebocoran Pipa', $response->json('data.complaint_type.name'));
    }

    public function test_unauthenticated_returns_401(): void
    {
        $user = $this->createCustomerUser();
        $complaint = $this->createComplaint($user);

        $response = $this->getJson("/api/v1/complaints/{$complaint->id}");

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

        $response = $this->actingAs($user)->getJson("/api/v1/complaints/{$complaint->id}");

        $response->assertStatus(403);
    }
}
