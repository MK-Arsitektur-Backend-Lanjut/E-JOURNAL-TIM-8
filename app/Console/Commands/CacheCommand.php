<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheCommand extends Command
{
    protected $signature = 'cache:manage 
                          {action : Action to perform (flush, stats, keys)}
                          {pattern? : Pattern untuk filter (optional)}';

    protected $description = 'Manage Redis cache untuk Digital Library';

    public function handle()
    {
        $action = $this->argument('action');
        $pattern = $this->argument('pattern');

        match ($action) {
            'flush' => $this->flush(),
            'stats' => $this->stats(),
            'keys' => $this->keys($pattern),
            'invalidate-catalog' => $this->invalidateCatalog(),
            'invalidate-recommendations' => $this->invalidateRecommendations(),
            default => $this->error("Action '{$action}' tidak dikenal."),
        };
    }

    private function flush(): void
    {
        if ($this->confirm('Yakin ingin hapus SEMUA cache?')) {
            Cache::flush();
            $this->info('✅ Semua cache berhasil dihapus');
        }
    }

    private function stats(): void
    {
        try {
            $redis = Cache::store('redis')->connection();
            $info = $redis->info();

            $this->info("\n📊 Redis Cache Statistics:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Memory Used', $info['used_memory_human'] ?? 'N/A'],
                    ['Connected Clients', $info['connected_clients'] ?? 0],
                    ['Total Commands', $info['total_commands_processed'] ?? 0],
                    ['Keys in DB', $redis->dbsize()],
                    ['Redis Version', $info['redis_version'] ?? 'N/A'],
                ]
            );
        } catch (\Exception $e) {
            $this->error('❌ Gagal terhubung ke Redis: ' . $e->getMessage());
        }
    }

    private function keys($pattern = null): void
    {
        try {
            $redis = Cache::store('redis')->connection();
            $prefix = Cache::getPrefix();
            $searchPattern = $prefix . ($pattern ?? '*');
            $keys = $redis->keys($searchPattern);

            if (empty($keys)) {
                $this->info('Tidak ada key yang cocok');
                return;
            }

            $this->info("\n🔑 Cache Keys (Total: " . count($keys) . ")");
            foreach ($keys as $key) {
                $displayKey = str_replace($prefix, '', $key);
                $ttl = $redis->ttl($key);
                $ttlText = $ttl === -1 ? 'No expiry' : $ttl . 's';
                $this->line("  ◦ $displayKey (TTL: $ttlText)");
            }
        } catch (\Exception $e) {
            $this->error('❌ Gagal terhubung ke Redis');
        }
    }

    private function invalidateCatalog(): void
    {
        CacheService::invalidateCatalog();
        $this->info('✅ Catalog cache (authors & tags) berhasil di-invalidate');
    }

    private function invalidateRecommendations(): void
    {
        CacheService::invalidateAllRecommendations();
        $this->info('✅ Semua recommendations cache berhasil di-invalidate');
    }
}
