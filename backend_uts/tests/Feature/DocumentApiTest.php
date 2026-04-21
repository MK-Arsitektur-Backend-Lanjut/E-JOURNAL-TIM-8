<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_documents(): void
    {
        Document::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/documents?per_page=10');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => ['data', 'current_page', 'per_page', 'total'],
            ]);
    }

    public function test_can_get_document_detail(): void
    {
        $document = Document::factory()->create();

        $response = $this->getJson('/api/v1/documents/' . $document->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $document->id);
    }

    public function test_can_create_document_with_upload_and_tags(): void
    {
        Storage::fake('public');

        $author = Author::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $payload = [
            'title' => 'Digital Library Collection',
            'author_id' => $author->id,
            'year' => 2024,
            'abstract' => 'Testing document creation endpoint.',
            'tag_ids' => $tags->pluck('id')->all(),
            'file' => UploadedFile::fake()->create('sample.pdf', 200),
        ];

        $response = $this->postJson('/api/v1/documents', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true);

        $documentId = $response->json('data.id');
        $storedPath = $response->json('data.file_path');

        $this->assertDatabaseHas('documents', ['id' => $documentId]);
        $this->assertDatabaseCount('document_tag', 2);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_can_update_document_and_sync_tags(): void
    {
        $document = Document::factory()->create();
        $newTags = Tag::factory()->count(3)->create();

        $payload = [
            'title' => 'Updated title',
            'tag_ids' => $newTags->pluck('id')->all(),
        ];

        $response = $this->putJson('/api/v1/documents/' . $document->id, $payload);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Updated title');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated title',
        ]);
        $this->assertDatabaseCount('document_tag', 3);
    }

    public function test_can_delete_document(): void
    {
        $document = Document::factory()->create();

        $response = $this->deleteJson('/api/v1/documents/' . $document->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_validation_error_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/documents', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'year']);
    }
}
