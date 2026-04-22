<?php

namespace App\Repositories;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        private readonly Subscription $model
    ) {}

    // =========================================================================
    // CACHE HELPERS
    // =========================================================================

    /**
     * Buat cache key yang konsisten dan aman untuk Redis di Docker.
     * Format: {prefix}.user.{userId}.{jenis}
     */
    private function cacheKey(int $userId, string $type): string
    {
        $prefix = config('plans.cache_prefix', 'subscription');
        return "{$prefix}.user.{$userId}.{$type}";
    }

    /**
     * Hapus semua cache terkait user ini.
     * Dipanggil setiap kali status langganan user berubah.
     */
    private function clearUserCache(int $userId): void
    {
        Cache::forget($this->cacheKey($userId, 'valid'));
        Cache::forget($this->cacheKey($userId, 'active'));
    }

    // =========================================================================
    // QUERY METHODS
    // =========================================================================

    /**
     * {@inheritDoc}
     *
     * Dilengkapi dengan Redis Cache untuk mendukung akses simultan
     * (pencarian metadata + download) tanpa membebani database.
     *
     * Cara kerja:
     * - Request pertama  → query DB → hasil disimpan ke cache (5 menit)
     * - Request berikutnya dalam 5 menit → langsung dari cache, DB tidak dipanggil
     * - Cache otomatis dihapus saat status langganan berubah (create/cancel/extend)
     */
    public function isValidForDownload(int $userId): bool
    {
        $ttl = now()->addMinutes(config('plans.cache_ttl_minutes', 5));

        return Cache::remember(
            $this->cacheKey($userId, 'valid'),
            $ttl,
            fn () => $this->model
                ->where('user_id', $userId)
                ->where('status', SubscriptionStatus::Active)
                ->where('started_at', '<=', now())
                ->where(fn ($q) => $q
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now())
                )
                ->exists()
        );
    }

    /**
     * {@inheritDoc}
     *
     * Di-cache agar detail langganan aktif tidak di-query berulang
     * saat user melakukan banyak aksi dalam waktu singkat.
     */
    public function findActiveByUser(int $userId): ?Subscription
    {
        $ttl = now()->addMinutes(config('plans.cache_ttl_minutes', 5));

        return Cache::remember(
            $this->cacheKey($userId, 'active'),
            $ttl,
            fn () => $this->model
                ->where('user_id', $userId)
                ->where('status', SubscriptionStatus::Active)
                ->where('started_at', '<=', now())
                ->where(fn ($q) => $q
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now())
                )
                ->latest('started_at')
                ->first()
        );
    }

    /**
     * {@inheritDoc}
     * Riwayat tidak di-cache karena jarang diakses & datanya berubah-ubah.
     */
    public function getAllByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * {@inheritDoc}
     * Cache dihapus setelah langganan baru dibuat agar isValidForDownload
     * langsung mengembalikan hasil terbaru.
     */
    public function create(array $data): Subscription
    {
        $subscription = $this->model->create($data);

        $this->clearUserCache($data['user_id']);

        return $subscription;
    }

    /**
     * {@inheritDoc}
     * Cache user di-invalidate agar perubahan status langsung terdeteksi.
     */
    public function updateStatus(int $subscriptionId, SubscriptionStatus $status): bool
    {
        $subscription = $this->model->find($subscriptionId);

        if (! $subscription) {
            return false;
        }

        $result = $subscription->update(['status' => $status]);

        $this->clearUserCache($subscription->user_id);

        return (bool) $result;
    }

    /**
     * {@inheritDoc}
     * Cache dihapus setelah perpanjangan agar sisa hari terupdate.
     */
    public function extend(int $subscriptionId, int $days, ?string $plan = null): bool
    {
        $subscription = $this->model->findOrFail($subscriptionId);

        $result = $subscription->extend($days, $plan);

        $this->clearUserCache($subscription->user_id);

        return $result;
    }

    /**
     * {@inheritDoc}
     * Batch update — tidak invalidate per user karena jumlahnya bisa ribuan.
     * Cache akan expired sendiri sesuai TTL.
     */
    public function expireOverdue(): int
    {
        return $this->model
            ->where('status', SubscriptionStatus::Active)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => SubscriptionStatus::Expired]);
    }
}
