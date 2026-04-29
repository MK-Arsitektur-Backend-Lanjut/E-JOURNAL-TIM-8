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
        $query = $this->model->newQuery()->with(['author', 'tags']);

        if (!empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['author'])) {
            $query->whereHas('author', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['author'] . '%');
            });
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['tag'] . '%');
            });
        }

        return $query->latest()->paginate($perPage);
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
        $query = $this->model->newQuery()->with(['author', 'tags']);

        if (!empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        if (!empty($filters['author'])) {
            $query->whereHas('author', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['author'] . '%');
            });
        }

        if (!empty($filters['abstract'])) {
            $query->where('abstract', 'like', '%' . $filters['abstract'] . '%');
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        return $query->paginate($perPage);
    }


    public function getRecommendations(int $documentId, int $limit = 5)
    {
        $document = $this->model->with('tags')->find($documentId);

        if (!$document || $document->tags->isEmpty()) {
            return collect([]);
        }

        $tagIds = $document->tags->pluck('id')->all();

        return $this->model
            ->with(['author', 'tags'])
            ->where('id', '!=', $documentId)
            ->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            })
            ->withCount(['tags as shared_tags_count' => function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            }])
            ->orderByDesc('shared_tags_count')
            ->orderByRaw('ABS(year - ?) ASC', [$document->year])
            ->limit($limit)
            ->get();
    }
}
