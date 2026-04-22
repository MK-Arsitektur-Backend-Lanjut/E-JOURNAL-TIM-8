<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorIds = Author::query()->pluck('id')->all();
        $tagIds = Tag::query()->pluck('id')->all();

        Document::factory()
            ->count(10000)
            ->state(function () use ($authorIds): array {
                return [
                    'author_id' => empty($authorIds) ? null : fake()->randomElement($authorIds),
                ];
            })
            ->create()
            ->each(function (Document $document) use ($tagIds): void {
                if (empty($tagIds)) {
                    return;
                }

                $picked = fake()->randomElements($tagIds, random_int(1, min(3, count($tagIds))));
                $document->tags()->sync($picked);
            });
    }
}
