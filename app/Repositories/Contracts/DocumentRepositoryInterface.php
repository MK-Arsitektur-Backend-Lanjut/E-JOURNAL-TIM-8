<?php

namespace App\Repositories\Contracts;

interface DocumentRepositoryInterface
{
    /**
     * Search documents based on advanced criteria.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters);

    /**
     * Get related documents recommendations based on a document ID.
     *
     * @param int $documentId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendations(int $documentId, int $limit = 5);

    /**
     * Get a single document by ID.
     *
     * @param int $id
     * @return \App\Models\Document|null
     */
    public function find(int $id);
}
