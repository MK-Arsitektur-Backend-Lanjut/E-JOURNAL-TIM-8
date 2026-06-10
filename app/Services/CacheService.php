<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

/**
 * Service untuk manajemen cache Redis
 * Menangani cache keys, TTL, dan invalidation
 */
class CacheService
{
    // Cache key prefixes
    const PREFIX_AUTHORS = 'catalog:authors';
    const PREFIX_TAGS = 'catalog:tags';
    const PREFIX_DOCUMENT = 'doc:';
    const PREFIX_RECOMMENDATIONS = 'recommendations:';
    const PREFIX_STATS = 'stats:';

    // Cache TTL (dalam detik)
    const TTL_CATALOG = 86400; // 24 jam - data master jarang berubah
    const TTL_DOCUMENT = 3600; // 1 jam - dokumen bisa diupdate
    const TTL_RECOMMENDATIONS = 1800; // 30 menit - computed heavy query
    const TTL_STATS = 300; // 5 menit - statistik sering berubah

    /**
     * Get all authors with cache
     */
    public static function getAuthors(callable $callback)
    {
        return Cache::remember(
            self::PREFIX_AUTHORS,
            self::TTL_CATALOG,
            $callback
        );
    }

    /**
     * Get all tags with cache
     */
    public static function getTags(callable $callback)
    {
        return Cache::remember(
            self::PREFIX_TAGS,
            self::TTL_CATALOG,
            $callback
        );
    }

    /**
     * Get single document with cache
     */
    public static function getDocument(int $documentId, callable $callback)
    {
        return Cache::remember(
            self::PREFIX_DOCUMENT . $documentId,
            self::TTL_DOCUMENT,
            $callback
        );
    }

    /**
     * Get document recommendations with cache
     */
    public static function getRecommendations(int $documentId, callable $callback)
    {
        return Cache::remember(
            self::PREFIX_RECOMMENDATIONS . $documentId,
            self::TTL_RECOMMENDATIONS,
            $callback
        );
    }

    /**
     * Invalidate catalog cache (authors & tags)
     * Dipanggil saat ada perubahan pada authors atau tags
     */
    public static function invalidateCatalog()
    {
        Cache::forget(self::PREFIX_AUTHORS);
        Cache::forget(self::PREFIX_TAGS);
    }

    /**
     * Invalidate document cache
     * Dipanggil saat document diupdate/dihapus
     */
    public static function invalidateDocument(int $documentId)
    {
        Cache::forget(self::PREFIX_DOCUMENT . $documentId);
        // Invalidate recommendations dari dokumen lain yang mungkin mereferensi dokumen ini
        self::invalidateAllRecommendations();
    }

    /**
     * Invalidate recommendations cache untuk document tertentu
     */
    public static function invalidateRecommendations(int $documentId)
    {
        Cache::forget(self::PREFIX_RECOMMENDATIONS . $documentId);
    }

     /**
     * Invalidate ALL recommendations cache
     * Dipanggil saat ada perubahan dokumen atau tags
     * ⚠️ Expensive operation - gunakan dengan hati-hati
     */
    public static function invalidateAllRecommendations()
    {
        try {
            // Jika driver utama bukan redis dan kita sedang mengetes (ArrayStore), abaikan
            if (config('cache.default') !== 'redis' && Cache::getStore() instanceof \Illuminate\Cache\ArrayStore) {
                return;
            }

            // Get all recommendation keys dan delete
            // Untuk Redis, kita bisa gunakan pattern matching
            $keys = Redis::connection()->keys(
                Cache::getPrefix() . self::PREFIX_RECOMMENDATIONS . '*'
            );
            
            foreach ($keys as $key) {
                Cache::forget(str_replace(Cache::getPrefix(), '', $key));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to invalidate Redis recommendations cache: " . $e->getMessage());
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStats()
    {
        return Cache::remember(
            self::PREFIX_STATS . 'overview',
            self::TTL_STATS,
            function () {
                try {
                    // Jika bukan redis, kembalikan stats kosong
                    if (config('cache.default') !== 'redis' && Cache::getStore() instanceof \Illuminate\Cache\ArrayStore) {
                        return [
                            'memory_usage' => 'N/A (Array Cache)',
                            'connected_clients' => 0,
                            'total_commands_processed' => 0,
                            'keys_in_db' => 0,
                        ];
                    }

                    $redis = Redis::connection();
                    $info = $redis->info();
                    
                    return [
                        'memory_usage' => $info['used_memory_human'] ?? 'N/A',
                        'connected_clients' => $info['connected_clients'] ?? 0,
                        'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                        'keys_in_db' => $redis->dbsize(),
                    ];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to get Redis stats: " . $e->getMessage());
                    return [
                        'memory_usage' => 'Offline',
                        'connected_clients' => 0,
                        'total_commands_processed' => 0,
                        'keys_in_db' => 0,
                    ];
                }
            }
        );
    }

    /**
     * Flush all cache (nuclear option)
     */
    public static function flushAll()
    {
        Cache::flush();
    }
}
