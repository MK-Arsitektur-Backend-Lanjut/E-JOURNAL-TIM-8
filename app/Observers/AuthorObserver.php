<?php

namespace App\Observers;

use App\Models\Author;
use App\Services\CacheService;

class AuthorObserver
{
    public function created(Author $author): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }

    public function updated(Author $author): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }

    public function deleted(Author $author): void
    {
        CacheService::invalidateCatalog();
        CacheService::invalidateAllRecommendations();
    }
}