<?php

namespace Tests\Feature\Api\V1\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_list_own_complaints_returns_paginated_data(): void
    {
        $user = $this->createCustomerUser();
        $this->createComplaint($user);
        $this->createComplaint($user, ['judul_pengaduan' => 'Pipa Bocor']);

        $response = $this->actingAs($user)->getJson('/api/v1/complaints');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'no_pengaduan',
                        'complaint_type',
                        'no_sambungan',
                        'judul_pengaduan',
                        'status',
                        'priority',
                        'tanggal',
                        'created_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_only_returns_own_complaints(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser(['email' => 'other@example.com', 'name' => 'Other User']);

        $complaintA = $this->createComplaint($userA, ['judul_pengaduan' => 'User A Complaint']);
        $this->createComplaint($userB, ['judul_pengaduan' => 'User B Complaint']);

        $response = $this->actingAs($userA)->getJson('/api/v1/complaints');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
        $this->assertEquals($complaintA->id, $response->json('data.0.id'));
    }

    public function test_empty_list_returns_empty_data(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/complaints');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_pagination_works_correctly(): void
    {
        $user = $this->createCustomerUser();
        for ($i = 0; $i < 8; $i++) {
            $this->createComplaint($user, ['judul_pengaduan' => "Complaint {$i}"]);
        }

        $response = $this->actingAs($user)->getJson('/api/v1/complaints?per_page=5&page=2');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 8);
    }

    public function test_clamps_per_page_to_100(): void
    {
        $user = $this->createCustomerUser();
        $this->createComplaint($user);

        $response = $this->actingAs($user)->getJson('/api/v1/complaints?per_page=200');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_complaints_ordered_most_recent_first(): void
    {
        $user = $this->createCustomerUser();
        $older = $this->createComplaint($user, ['judul_pengaduan' => 'Older']);

        $this->travel(1)->seconds();
        $newer = $this->createComplaint($user, ['judul_pengaduan' => 'Newer']);

        $response = $this->actingAs($user)->getJson('/api/v1/complaints');

        $response->assertStatus(200);
        $this->assertEquals($newer->id, $response->json('data.0.id'));
        $this->assertEquals($older->id, $response->json('data.1.id'));
    }

    public function test_includes_complaint_type(): void
    {
        $user = $this->createCustomerUser();
        $this->createComplaint($user);

        $response = $this->actingAs($user)->getJson('/api/v1/complaints');

        $response->assertStatus(200);
        $this->assertEquals($this->complaintType->id, $response->json('data.0.complaint_type.id'));
        $this->assertEquals('Kebocoran Pipa', $response->json('data.0.complaint_type.name'));
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/complaints');

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/complaints');

        $response->assertStatus(403);
    }
}
