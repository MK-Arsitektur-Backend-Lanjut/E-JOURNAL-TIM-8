<?php

namespace App\Observers;

use App\Models\Tag;
use App\Services\CacheService;

class TagObserver
{
    public function created(Tag $tag): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }

    public function updated(Tag $tag): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }

    public function deleted(Tag $tag): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }
}