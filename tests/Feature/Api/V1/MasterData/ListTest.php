<?php

namespace Tests\Feature\Api\V1\MasterData;

use App\Models\ComplaintType;
use App\Models\District;
use App\Models\Program;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class ListTest extends TestCase
{
    use RefreshDatabaseCompat;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer', 'web');
        Cache::flush();
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

    // ── Programs ──────────────────────────────────────────────

    public function test_list_active_programs(): void
    {
        $user = $this->createCustomerUser();
        Program::create(['name' => 'Program A', 'is_active' => true]);
        Program::create(['name' => 'Program B', 'is_active' => true]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/programs');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_excludes_inactive_programs(): void
    {
        $user = $this->createCustomerUser();
        Program::create(['name' => 'Active', 'is_active' => true]);
        Program::create(['name' => 'Inactive', 'is_active' => false]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/programs');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active');
    }

    public function test_programs_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/programs');

        $response->assertStatus(401);
    }

    public function test_programs_non_customer_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/programs');

        $response->assertStatus(403);
    }

    // ── Provinces ─────────────────────────────────────────────

    public function test_list_selectable_provinces(): void
    {
        $user = $this->createCustomerUser();
        Province::create(['name' => 'Jawa Barat', 'is_selectable' => true]);
        Province::create(['name' => 'Jawa Timur', 'is_selectable' => true]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/provinces');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_excludes_non_selectable_provinces(): void
    {
        $user = $this->createCustomerUser();
        Province::create(['name' => 'Selectable', 'is_selectable' => true]);
        Province::create(['name' => 'Hidden', 'is_selectable' => false]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/provinces');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Selectable');
    }

    public function test_provinces_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/provinces');

        $response->assertStatus(401);
    }

    // ── Regencies ─────────────────────────────────────────────

    public function test_list_regencies_for_province(): void
    {
        $user = $this->createCustomerUser();
        $province = Province::create(['name' => 'Jawa Barat', 'is_selectable' => true]);
        Regency::create(['province_id' => $province->id, 'name' => 'Bandung', 'is_selectable' => true]);
        Regency::create(['province_id' => $province->id, 'name' => 'Bogor', 'is_selectable' => true]);

        $response = $this->actingAs($user)->getJson("/api/v1/master/regencies/{$province->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_regencies_excludes_other_provinces(): void
    {
        $user = $this->createCustomerUser();
        $provinceA = Province::create(['name' => 'Province A', 'is_selectable' => true]);
        $provinceB = Province::create(['name' => 'Province B', 'is_selectable' => true]);
        Regency::create(['province_id' => $provinceA->id, 'name' => 'Regency A', 'is_selectable' => true]);
        Regency::create(['province_id' => $provinceB->id, 'name' => 'Regency B', 'is_selectable' => true]);

        $response = $this->actingAs($user)->getJson("/api/v1/master/regencies/{$provinceA->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Regency A');
    }

    public function test_regencies_empty_for_nonexistent_province(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/master/regencies/99999');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_regencies_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/regencies/1');

        $response->assertStatus(401);
    }

    // ── Districts ─────────────────────────────────────────────

    public function test_list_districts_for_regency(): void
    {
        $user = $this->createCustomerUser();
        $province = Province::create(['name' => 'Jawa Barat', 'is_selectable' => true]);
        $regency = Regency::create(['province_id' => $province->id, 'name' => 'Bandung', 'is_selectable' => true]);
        District::create(['regency_id' => $regency->id, 'name' => 'Coblong']);
        District::create(['regency_id' => $regency->id, 'name' => 'Dago']);

        $response = $this->actingAs($user)->getJson("/api/v1/master/districts/{$regency->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_districts_empty_for_nonexistent_regency(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/master/districts/99999');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_districts_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/districts/1');

        $response->assertStatus(401);
    }

    // ── Villages ──────────────────────────────────────────────

    public function test_list_villages_for_district(): void
    {
        $user = $this->createCustomerUser();
        $province = Province::create(['name' => 'Jawa Barat', 'is_selectable' => true]);
        $regency = Regency::create(['province_id' => $province->id, 'name' => 'Bandung', 'is_selectable' => true]);
        $district = District::create(['regency_id' => $regency->id, 'name' => 'Coblong']);
        Village::create(['district_id' => $district->id, 'name' => 'Dago']);
        Village::create(['district_id' => $district->id, 'name' => 'Lebak Siliwangi']);

        $response = $this->actingAs($user)->getJson("/api/v1/master/villages/{$district->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_villages_empty_for_nonexistent_district(): void
    {
        $user = $this->createCustomerUser();

        $response = $this->actingAs($user)->getJson('/api/v1/master/villages/99999');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_villages_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/villages/1');

        $response->assertStatus(401);
    }

    // ── Complaint Types ───────────────────────────────────────

    public function test_list_active_complaint_types(): void
    {
        $user = $this->createCustomerUser();
        ComplaintType::create(['name' => 'Kebocoran Pipa', 'is_active' => true]);
        ComplaintType::create(['name' => 'Air Keruh', 'is_active' => true]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/complaint-types');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(2, 'data');
    }

    public function test_excludes_inactive_complaint_types(): void
    {
        $user = $this->createCustomerUser();
        ComplaintType::create(['name' => 'Active Type', 'is_active' => true]);
        ComplaintType::create(['name' => 'Inactive Type', 'is_active' => false]);

        $response = $this->actingAs($user)->getJson('/api/v1/master/complaint-types');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active Type');
    }

    public function test_complaint_types_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/master/complaint-types');

        $response->assertStatus(401);
    }

    // ── Caching ───────────────────────────────────────────────

    public function test_responses_are_cached(): void
    {
        $user = $this->createCustomerUser();
        Program::create(['name' => 'Test Program', 'is_active' => true]);

        $this->assertFalse(Cache::has('master.programs'));

        $this->actingAs($user)->getJson('/api/v1/master/programs')->assertStatus(200);

        $this->assertTrue(Cache::has('master.programs'));
    }
}
