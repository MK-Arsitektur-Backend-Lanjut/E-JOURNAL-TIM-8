<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'author' => $this->faker->name(),
            'year' => $this->faker->year(),
            'abstract' => $this->faker->paragraph(3),
            'tags' => $this->faker->randomElements(['technology', 'science', 'history', 'art', 'business', 'medicine', 'engineering', 'literature'], 3),
            'file_path' => 'dummy_assets/sample.pdf',
        ];
    }
}
