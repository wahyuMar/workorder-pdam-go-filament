<?php

namespace Tests\Feature;

use App\Enums\BeritaKategori;
use App\Models\Berita;
use App\Models\User;
use Tests\Concerns\RefreshDatabaseCompat;
use Tests\TestCase;

class BeritaTest extends TestCase
{
    use RefreshDatabaseCompat;

    // --- API Tests ---

    public function test_api_index_returns_only_published_beritas(): void
    {
        Berita::factory()->published()->count(3)->create();
        Berita::factory()->create(['is_publish' => false]);

        $response = $this->getJson('/api/v1/beritas');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'judul', 'kategori', 'kategori_label', 'image', 'is_publish', 'created_at'],
                ],
            ]);
    }

    public function test_api_index_filters_by_kategori(): void
    {
        Berita::factory()->published()->create(['kategori' => BeritaKategori::News]);
        Berita::factory()->published()->create(['kategori' => BeritaKategori::Pengumuman]);

        $response = $this->getJson('/api/v1/beritas?kategori=news');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_show_returns_published_berita(): void
    {
        $berita = Berita::factory()->published()->create();

        $response = $this->getJson("/api/v1/beritas/{$berita->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'judul', 'kategori', 'content', 'image', 'file_lampiran', 'is_publish', 'created_at'],
            ])
            ->assertJsonPath('data.id', $berita->id);
    }

    public function test_api_show_returns_404_for_unpublished_berita(): void
    {
        $berita = Berita::factory()->create(['is_publish' => false]);

        $this->getJson("/api/v1/beritas/{$berita->id}")
            ->assertNotFound();
    }

    public function test_api_index_paginates_results(): void
    {
        Berita::factory()->published()->count(20)->create();

        $response = $this->getJson('/api/v1/beritas?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    // --- Model Tests ---

    public function test_berita_model_casts_kategori_to_enum(): void
    {
        $berita = Berita::factory()->create(['kategori' => BeritaKategori::News]);

        $this->assertInstanceOf(BeritaKategori::class, $berita->fresh()->kategori);
        $this->assertSame(BeritaKategori::News, $berita->fresh()->kategori);
    }

    public function test_berita_author_relationship(): void
    {
        $author = User::factory()->create();
        $berita = Berita::factory()->create(['created_by' => $author->id]);

        $this->assertEquals($author->id, $berita->author->id);
    }

    public function test_berita_enum_options(): void
    {
        $options = BeritaKategori::options();

        $this->assertArrayHasKey('news', $options);
        $this->assertArrayHasKey('pengumuman', $options);
        $this->assertEquals('News', $options['news']);
        $this->assertEquals('Pengumuman', $options['pengumuman']);
    }
}
