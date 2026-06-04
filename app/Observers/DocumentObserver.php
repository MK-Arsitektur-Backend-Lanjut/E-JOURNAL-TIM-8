<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\CacheService;

/**
 * Observer untuk Document model
 * Otomatis invalidate cache saat dokumen diubah
 */
class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        // Invalidate all recommendations karena ada dokumen baru
        CacheService::invalidateAllRecommendations();
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        // Invalidate cache dokumen ini
        CacheService::invalidateDocument($document->id);
        // Invalidate recommendations yang referensi dokumen ini
        CacheService::invalidateRecommendations($document->id);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        // Invalidate cache dokumen
        CacheService::invalidateDocument($document->id);
        // Invalidate semua recommendations
        CacheService::invalidateAllRecommendations();
    }
}
