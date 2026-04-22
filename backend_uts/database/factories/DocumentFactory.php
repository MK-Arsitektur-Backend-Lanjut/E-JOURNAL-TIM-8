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
            'abstract' => $this->faker->paragraphs(2, true),
            'year' => $this->faker->numberBetween(1990, (int) date('Y')),
            'file_path' => 'documents/dummy_' . $this->faker->unique()->numberBetween(1, 100000) . '.pdf',
            'author_id' => null,
        ];
    }
}
