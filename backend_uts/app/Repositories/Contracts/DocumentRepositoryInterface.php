<?php

namespace App\Repositories\Contracts;

interface DocumentRepositoryInterface
{
    /**
     * Get paginated list of documents.
     */
    public function getAll(array $filters = [], int $perPage = 15);

    /**
     * Find a document by ID.
     */
    public function findById(int $id);

    /**
     * Create a new document.
     */
    public function create(array $data);

    /**
     * Update an existing document by ID.
     */
    public function update(int $id, array $data);

    /**
     * Delete a document by ID.
     */
    public function delete(int $id): bool;

    /**
     * Search documents based on advanced criteria.
     */
    public function search(array $filters, int $perPage = 15);

    /**
     * Get related documents recommendations based on a document ID.
     */
    public function getRecommendations(int $documentId, int $limit = 5);
}
