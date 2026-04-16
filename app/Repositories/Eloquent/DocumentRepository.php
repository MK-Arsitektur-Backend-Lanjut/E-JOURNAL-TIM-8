<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class DocumentRepository implements DocumentRepositoryInterface
{
    protected $model;

    public function __construct(Document $model)
    {
        $this->model = $model;
    }

    public function search(array $filters)
    {
        $query = $this->model->newQuery();

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (!empty($filters['author'])) {
            $query->where('author', 'like', '%' . $filters['author'] . '%');
        }

        if (!empty($filters['abstract'])) {
            $query->where('abstract', 'like', '%' . $filters['abstract'] . '%');
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        return $query->paginate(15);
    }

    public function getRecommendations(int $documentId, int $limit = 5)
    {
        $document = $this->find($documentId);
        
        if (!$document || empty($document->tags)) {
            return collect([]);
        }

        $query = $this->model->where('id', '!=', $documentId)
            ->where(function($q) use ($document) {
                // Simplified tag matching using JSON constraints
                // SQLite in basic setup might lack native JSON intersect, so we fallback or loop
                // Since this is generic, we can do whereJsonContains for one tag, or we do a simple match
                // We'll iterate the tags and use orWhereJsonContains
                foreach ($document->tags as $tag) {
                    // For SQLite, JSON where statements might require the column to be real JSON. 
                    // This is handled by Laravel's whereJsonContains.
                    $q->orWhereJsonContains('tags', $tag);
                }
            });

        return $query->limit($limit)->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }
}
