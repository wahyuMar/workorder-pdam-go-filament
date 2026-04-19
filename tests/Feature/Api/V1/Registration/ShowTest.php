<?php

namespace Tests\Feature\Api\V1\Registration;

use App\Models\CustomerRegistration;
use App\Models\Survey;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_view_own_registration_detail(): void
    {
        $user = $this->createCustomerUser();
        $registration = $this->createRegistration($user, [
            'nama_lengkap' => 'Budi Santoso',
            'no_ktp' => '3275012345670003',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'no_surat',
                    'nama_lengkap',
                    'program_id',
                    'no_ktp',
                    'source',
                    'tanggal',
                    'created_at',
                    'has_survey',
                ],
            ])
            ->assertJsonPath('data.id', $registration->id)
            ->assertJsonPath('data.nama_lengkap', 'Budi Santoso');
    }

    public function test_cannot_view_another_users_registration(): void
    {
        $userA = $this->createCustomerUser();
        $userB = $this->createCustomerUser(['email' => 'other@example.com', 'name' => 'Other User']);

        $registration = $this->createRegistration($userB);

        $response = $this->actingAs($userA)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(404);
    }

    public function test_non_existent_registration_returns_404(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/registrations/99999');

        $response->assertStatus(404);
    }

    public function test_nik_masked_in_detail_response(): void
    {
        $user = $this->createCustomerUser();
        $registration = $this->createRegistration($user, [
            'no_ktp' => '3275012345670003',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(200);
        $this->assertEquals('3275****0003', $response->json('data.no_ktp'));

        $this->assertDatabaseHas('customer_registrations', [
            'no_ktp' => '3275012345670003',
        ]);
    }

    public function test_registration_with_survey_shows_survey_data(): void
    {
        $user = $this->createCustomerUser();
        $registration = $this->createRegistration($user);

        Survey::create([
            'customer_registration_id' => $registration->id,
            'no_survey' => 'SRV-00001',
            'tanggal_survey' => now(),
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_survey', true)
            ->assertJsonStructure([
                'data' => [
                    'survey' => ['no_survey', 'tanggal_survey'],
                ],
            ])
            ->assertJsonPath('data.survey.no_survey', 'SRV-00001');
    }

    public function test_registration_without_survey_shows_null(): void
    {
        $user = $this->createCustomerUser();
        $registration = $this->createRegistration($user);

        $response = $this->actingAs($user)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.has_survey', false);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $user = $this->createCustomerUser();
        $registration = $this->createRegistration($user);

        $response = $this->getJson("/api/v1/registrations/{$registration->id}");

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
        $registration = $this->createRegistration($customerUser);

        $response = $this->actingAs($user)->getJson("/api/v1/registrations/{$registration->id}");

        $response->assertStatus(403);
    }
}
