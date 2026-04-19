<?php

namespace Tests\Feature\Api\V1\Registration;

use App\Models\CustomerRegistration;
use App\Models\Survey;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabaseCompat;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
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

    private function createRegistration(User $user, array $overrides = []): CustomerRegistration
    {
        return CustomerRegistration::create(array_merge([
            'user_id' => $user->id,
            'source' => 'mobile',
            'no_surat' => 'SRPB-'.fake()->unique()->randomNumber(5),
            'nama_lengkap' => fake()->name(),
            'tanggal' => now(),
        ], $overrides));
    }

    public function test_list_own_registrations_returns_paginated_data(): void
    {
        $user = $this->createCustomerUser();
        $this->createRegistration($user);
        $this->createRegistration($user);

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'no_surat',
                        'nama_lengkap',
                        'program_id',
                        'no_ktp',
                        'source',
                        'tanggal',
                        'has_survey',
                        'created_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_only_returns_own_registrations(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser(['email' => 'other@example.com', 'name' => 'Other User']);

        $regA = $this->createRegistration($userA, ['nama_lengkap' => 'User A Reg']);
        $this->createRegistration($userB, ['nama_lengkap' => 'User B Reg']);

        $response = $this->actingAs($userA)->getJson('/api/v1/registrations');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
        $this->assertEquals($regA->id, $response->json('data.0.id'));
    }

    public function test_empty_state_returns_empty_data(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_pagination_works_correctly(): void
    {
        $user = $this->createCustomerUser();
        for ($i = 0; $i < 8; $i++) {
            $this->createRegistration($user);
        }

        $response = $this->actingAs($user)->getJson('/api/v1/registrations?per_page=5&page=2');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 8);
    }

    public function test_nik_masked_in_list_response(): void
    {
        $user = $this->createCustomerUser();
        $this->createRegistration($user, ['no_ktp' => '3275012345670003']);

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(200);
        $this->assertEquals('3275****0003', $response->json('data.0.no_ktp'));

        $this->assertDatabaseHas('customer_registrations', [
            'no_ktp' => '3275012345670003',
        ]);
    }

    public function test_has_survey_field_reflects_survey_existence(): void
    {
        $user = $this->createCustomerUser();
        $regWithSurvey = $this->createRegistration($user);
        $regWithout = $this->createRegistration($user);

        Survey::create([
            'customer_registration_id' => $regWithSurvey->id,
            'no_survey' => 'SRV-00001',
            'tanggal_survey' => now(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(200);

        $data = collect($response->json('data'));
        $withSurvey = $data->firstWhere('id', $regWithSurvey->id);
        $withoutSurvey = $data->firstWhere('id', $regWithout->id);

        $this->assertTrue($withSurvey['has_survey']);
        $this->assertFalse($withoutSurvey['has_survey']);
    }

    public function test_registrations_ordered_most_recent_first(): void
    {
        $user = $this->createCustomerUser();
        $older = $this->createRegistration($user, ['nama_lengkap' => 'Older']);

        $this->travel(1)->seconds();
        $newer = $this->createRegistration($user, ['nama_lengkap' => 'Newer']);

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(200);
        $this->assertEquals($newer->id, $response->json('data.0.id'));
        $this->assertEquals($older->id, $response->json('data.1.id'));
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/registrations');

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/registrations');

        $response->assertStatus(403);
    }
}
