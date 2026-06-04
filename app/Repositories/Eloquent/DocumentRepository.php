<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Arr;

class DocumentRepository implements DocumentRepositoryInterface
{
    protected $model;

    public function __construct(Document $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery()
            ->with(['author', 'tags'])
            ->select('documents.*');

        if (!empty($filters['year'])) {
            $query->where('documents.year', (int) $filters['year']);
        }

        if (!empty($filters['title'])) {
            $query->where('documents.title', 'like', $filters['title'] . '%');
        }

        if (!empty($filters['author'])) {
            $query->whereHas('author', function ($q) use ($filters) {
                $q->where('authors.name', 'like', $filters['author'] . '%');
            });
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.name', '=', $filters['tag']);
            });
        }

        return $query->latest('documents.created_at')->paginate($perPage);
    }

    public function findById(int $id)
    {
        return $this->model
            ->with(['author', 'tags'])
            ->find($id);
    }

    public function create(array $data)
    {
        $tagIds = Arr::pull($data, 'tag_ids', []);

        $document = $this->model->create($data);

        if (!empty($tagIds)) {
            $document->tags()->sync($tagIds);
        }

        return $document->load(['author', 'tags']);
    }

    public function update(int $id, array $data)
    {
        $document = $this->findById($id);

        if (!$document) {
            return null;
        }

        $hasTagIds = array_key_exists('tag_ids', $data);
        $tagIds = Arr::pull($data, 'tag_ids', []);

        $document->update($data);

        if ($hasTagIds) {
            $document->tags()->sync($tagIds);
        }

        return $document->fresh()->load(['author', 'tags']);
    }

    public function delete(int $id): bool
    {
        $document = $this->findById($id);

        if (!$document) {
            return false;
        }

        $document->tags()->detach();

        return (bool) $document->delete();
    }


    public function search(array $filters, int $perPage = 15)
    {
        $query = $this->model->newQuery()
            ->with(['author', 'tags'])
            ->select('documents.*');

        if (!empty($filters['year'])) {
            $query->where('documents.year', (int) $filters['year']);
        }

        if (!empty($filters['author'])) {
            $query->whereHas('author', function ($q) use ($filters) {
                $q->where('authors.name', 'like', $filters['author'] . '%');
            });
        }

        if (!empty($filters['abstract'])) {
            $query->where('documents.abstract', 'like', '%' . $filters['abstract'] . '%');
        }

        if (!empty($filters['title'])) {
            $query->where('documents.title', 'like', $filters['title'] . '%');
        }

        return $query->latest('documents.created_at')->paginate($perPage);
    }


    public function getRecommendations(int $documentId, int $limit = 5)
    {
        $document = $this->model->with('tags')->find($documentId);

        if (!$document || $document->tags->isEmpty()) {
            return collect([]);
        }

        $tagIds = $document->tags->pluck('id')->all();
        $documentYear = $document->year;

        return $this->model
            ->with(['author', 'tags'])
            ->select('documents.*')
            ->where('documents.id', '!=', $documentId)
            ->join('document_tag', 'documents.id', '=', 'document_tag.document_id')
            ->whereIn('document_tag.tag_id', $tagIds)
            ->selectRaw('COUNT(document_tag.tag_id) as shared_tags_count')
            ->selectRaw('ABS(documents.year - ?) as year_difference', [$documentYear])
            ->groupBy('documents.id')
            ->orderByDesc('shared_tags_count')
            ->orderBy('year_difference')
            ->limit($limit)
            ->get();
    }
}
