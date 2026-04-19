<?php

namespace Tests\Feature\Api\V1\Upload;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('public');
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

    public function test_successful_upload_jpg(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(500);

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['filename', 'original_name', 'size', 'mime_type'],
            ]);

        $this->assertDatabaseHas('uploads', [
            'user_id' => $user->id,
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $upload = Upload::first();
        Storage::disk('public')->assertExists($upload->stored_path);
    }

    public function test_successful_upload_png(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('image.png', 640, 480)->size(500);

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('uploads', [
            'user_id' => $user->id,
            'original_name' => 'image.png',
            'mime_type' => 'image/png',
        ]);
    }

    public function test_successful_upload_pdf(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('uploads', [
            'user_id' => $user->id,
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function test_response_format_matches_upload_resource(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(200);

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['filename', 'original_name', 'size', 'mime_type'],
            ])
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.user_id')
            ->assertJsonMissingPath('data.stored_path');

        $data = $response->json('data');
        $this->assertEquals('photo.jpg', $data['original_name']);
        $this->assertEquals('image/jpeg', $data['mime_type']);
        $this->assertIsInt($data['size']);
        $this->assertNotEmpty($data['filename']);
    }

    public function test_db_record_has_correct_fields(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(300);

        $this->postJson('/api/v1/uploads', ['file' => $file]);

        $upload = Upload::first();
        $this->assertNotNull($upload);
        $this->assertEquals($user->id, $upload->user_id);
        $this->assertEquals('photo.jpg', $upload->original_name);
        $this->assertStringStartsWith('uploads/', $upload->stored_path);
        $this->assertEquals('image/jpeg', $upload->mime_type);
        $this->assertGreaterThan(0, $upload->size);
    }

    public function test_file_too_large_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseCount('uploads', 0);
    }

    public function test_invalid_mime_type_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');

        $this->assertDatabaseCount('uploads', 0);
    }

    public function test_missing_file_returns_422(): void
    {
        $user = $this->createCustomerUser();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/uploads', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_unauthenticated_returns_401(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

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

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertStatus(403);
    }
}
