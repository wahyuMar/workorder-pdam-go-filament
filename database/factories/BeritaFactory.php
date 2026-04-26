<?php

namespace Database\Factories;

use App\Enums\BeritaKategori;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Berita>
 */
class BeritaFactory extends Factory
{
    protected $model = Berita::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(),
            'kategori' => fake()->randomElement(BeritaKategori::cases())->value,
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'image' => null,
            'file_lampiran' => null,
            'is_publish' => fake()->boolean(),
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(['is_publish' => true]);
    }
}
