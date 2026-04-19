<?php

namespace Tests\Feature\Api\V1\Registration;

use App\Models\CustomerRegistration;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class StoreTest extends TestCase
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

    public function test_successful_registration_with_minimal_data(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'no_surat',
                    'nama_lengkap',
                    'source',
                    'tanggal',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('customer_registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'user_id' => $user->id,
            'source' => 'mobile',
        ]);
    }

    public function test_successful_registration_with_full_data(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $data = [
            'nama_lengkap' => 'Budi Santoso',
            'no_ktp' => '3275012345670003',
            'no_kk' => '3275012345670001',
            'pekerjaan' => 'Wiraswasta',
            'email' => 'budi@example.com',
            'no_telp' => '021-12345678',
            'no_hp' => '08123456789',
            'alamat_ktp' => 'Jl. Merdeka No. 10',
            'dusun_kampung_ktp' => 'Kampung Baru',
            'rt_ktp' => 5,
            'rw_ktp' => 3,
            'alamat_pasang' => 'Jl. Merdeka No. 10',
            'dusun_kampung_pasang' => 'Kampung Baru',
            'rt_pasang' => 5,
            'rw_pasang' => 3,
            'jumlah_penghuni_tetap' => 4,
            'jumlah_penghuni_tidak_tetap' => 1,
            'jumlah_kran_air_minum' => 2,
            'jenis_rumah' => 'Permanen',
            'jumlah_kran' => 3,
            'daya_listrik' => 1300,
            'upload_ktp' => 'uploads/abc123.jpg',
            'upload_kk' => 'uploads/def456.jpg',
            'upload_tagihan_listrik' => 'uploads/ghi789.pdf',
            'upload_foto_rumah' => 'uploads/jkl012.jpg',
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ];

        $response = $this->postJson('/api/v1/registrations', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('customer_registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'no_ktp' => '3275012345670003',
            'jenis_rumah' => 'Permanen',
            'daya_listrik' => 1300,
            'upload_ktp' => 'uploads/abc123.jpg',
            'latitude' => '-6.200000',
        ]);
    }

    public function test_auto_generated_fields_are_set_correctly(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Siti Aminah',
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertStringStartsWith('SRPB-', $data['no_surat']);
        $this->assertNotNull($data['tanggal']);
        $this->assertEquals('mobile', $data['source']);

        $registration = CustomerRegistration::first();
        $this->assertEquals($user->id, $registration->user_id);
        $this->assertEquals('mobile', $registration->source);
        $this->assertNotNull($registration->no_surat);
        $this->assertNotNull($registration->tanggal);
    }

    public function test_upload_references_stored_as_strings(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'upload_ktp' => 'uploads/abc123.jpg',
            'upload_kk' => 'uploads/def456.png',
            'upload_tagihan_listrik' => 'uploads/ghi789.pdf',
            'upload_foto_rumah' => 'uploads/jkl012.jpg',
        ]);

        $response->assertStatus(201);

        $registration = CustomerRegistration::first();
        $this->assertEquals('uploads/abc123.jpg', $registration->upload_ktp);
        $this->assertEquals('uploads/def456.png', $registration->upload_kk);
        $this->assertEquals('uploads/ghi789.pdf', $registration->upload_tagihan_listrik);
        $this->assertEquals('uploads/jkl012.jpg', $registration->upload_foto_rumah);
    }

    public function test_nik_masked_in_response_but_full_in_database(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'no_ktp' => '3275012345670003',
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertEquals('3275****0003', $data['no_ktp']);

        $this->assertDatabaseHas('customer_registrations', [
            'no_ktp' => '3275012345670003',
        ]);
    }

    public function test_missing_nama_lengkap_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'no_ktp' => '3275012345670003',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('nama_lengkap');
    }

    public function test_invalid_program_id_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'program_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('program_id');
    }

    public function test_invalid_jenis_rumah_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'jenis_rumah' => 'Gubuk',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('jenis_rumah');
    }

    public function test_invalid_email_format_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_customer_user_returns_403(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/registrations', [
            'nama_lengkap' => 'Budi Santoso',
        ]);

        $response->assertStatus(403);
    }
}
