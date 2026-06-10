<?php

namespace App\Repositories\Eloquent;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CachedSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $repository,
        private readonly CacheRepository $cache
    ) {}

    /**
     * Get cache key prefix.
     */
    private function cacheKey(int $userId, string $type): string
    {
        $prefix = config('plans.cache_prefix', 'subscription');
        return "{$prefix}.user.{$userId}.{$type}";
    }

    /**
     * Clear subscription cache for a specific user.
     */
    private function clearUserCache(int $userId): void
    {
        $this->cache->forget($this->cacheKey($userId, 'valid'));
        $this->cache->forget($this->cacheKey($userId, 'active'));
    }

    /**
     * {@inheritDoc}
     *
     * Memeriksa validitas akses unduh user dengan proteksi Redis Cache & Mutex Lock.
     */
    public function isValidForDownload(int $userId): bool
    {
        $key = $this->cacheKey($userId, 'valid');
        $ttl = now()->addMinutes(config('plans.cache_ttl_minutes', 5));

        // Cek jika cache sudah ada
        if ($this->cache->has($key)) {
            return (bool) $this->cache->get($key);
        }

        // Mutex Lock untuk mencegah Cache Stampede (Thundering Herd)
        $lockKey = "lock.subscription.valid.user.{$userId}";
        $lock = $this->cache->lock($lockKey, 10); // Lock bertahan maksimal 10 detik

        try {
            // Coba dapatkan lock, tunggu maksimal 3 detik (block & wait)
            if ($lock->block(3)) {
                if ($this->cache->has($key)) {
                    return (bool) $this->cache->get($key);
                }

                $isValid = $this->repository->isValidForDownload($userId);
                $this->cache->put($key, $isValid, $ttl);

                return $isValid;
            }
        } catch (\Exception $e) {
            Log::error("Mutex lock error in isValidForDownload: " . $e->getMessage());
        } finally {
            $lock->release();
        }

        // Fallback jika lock gagal didapatkan: langsung query DB agar service tidak hang
        return $this->repository->isValidForDownload($userId);
    }

    /**
     * {@inheritDoc}
     *
     * Mendapatkan langganan aktif user dengan proteksi Redis Cache & Mutex Lock.
     */
    public function findActiveByUser(int $userId): ?Subscription
    {
        $key = $this->cacheKey($userId, 'active');
        $ttl = now()->addMinutes(config('plans.cache_ttl_minutes', 5));

        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $lockKey = "lock.subscription.active.user.{$userId}";
        $lock = $this->cache->lock($lockKey, 10);

        try {
            if ($lock->block(3)) {
                if ($this->cache->has($key)) {
                    return $this->cache->get($key);
                }

                $activeSubscription = $this->repository->findActiveByUser($userId);
                $this->cache->put($key, $activeSubscription, $ttl);

                return $activeSubscription;
            }
        } catch (\Exception $e) {
            Log::error("Mutex lock error in findActiveByUser: " . $e->getMessage());
        } finally {
            $lock->release();
        }

        return $this->repository->findActiveByUser($userId);
    }

    /**
     * {@inheritDoc}
     *
     * Riwayat tidak di-cache karena jarang diakses & datanya dinamis.
     */
    public function getAllByUser(int $userId): Collection
    {
        return $this->repository->getAllByUser($userId);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Subscription
    {
        $subscription = $this->repository->create($data);
        $this->clearUserCache($subscription->user_id);
        return $subscription;
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(int $subscriptionId, SubscriptionStatus $status): bool
    {
        // Cari subscription untuk tahu user_id-nya sebelum diupdate
        $subscription = Subscription::find($subscriptionId);
        $userId = $subscription ? $subscription->user_id : null;

        $result = $this->repository->updateStatus($subscriptionId, $status);

        if ($userId) {
            $this->clearUserCache($userId);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function extend(int $subscriptionId, int $days, ?string $plan = null): bool
    {
        $subscription = Subscription::find($subscriptionId);
        $userId = $subscription ? $subscription->user_id : null;

        $result = $this->repository->extend($subscriptionId, $days, $plan);

        if ($userId) {
            $this->clearUserCache($userId);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * Memproses kadaluarsa batch secara berurutan dan membersihkan cache user secara spesifik.
     */
    public function expireOverdue(): array
    {
        $expiredUserIds = $this->repository->expireOverdue();

        foreach ($expiredUserIds as $userId) {
            $this->clearUserCache($userId);
        }

        return $expiredUserIds;
    }
}
