<?php

namespace App\Repositories\Eloquent;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(
        private readonly Subscription $model
    ) {}

    // Caching helpers removed toCachedSubscriptionRepository decorator

    // =========================================================================
    // QUERY METHODS
    // =========================================================================

    public function isValidForDownload(int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatus::Active)
            ->where('started_at', '<=', now())
            ->where(fn ($q) => $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now())
            )
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function findActiveByUser(int $userId): ?Subscription
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatus::Active)
            ->where('started_at', '<=', now())
            ->where(fn ($q) => $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now())
            )
            ->latest('started_at')
            ->first();
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

    public function create(array $data): Subscription
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(int $subscriptionId, SubscriptionStatus $status): bool
    {
        $subscription = $this->model->find($subscriptionId);

        if (! $subscription) {
            return false;
        }

        return (bool) $subscription->update(['status' => $status]);
    }

    /**
     * {@inheritDoc}
     */
    public function extend(int $subscriptionId, int $days, ?string $plan = null): bool
    {
        $subscription = $this->model->findOrFail($subscriptionId);

        return $subscription->extend($days, $plan);
    }

    public function expireOverdue(): array
    {
        $expiredUserIds = [];

        do {
            $subscriptions = $this->model
                ->where('status', SubscriptionStatus::Active)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->limit(500)
                ->pluck('user_id', 'id');

            if ($subscriptions->isEmpty()) {
                break;
            }

            $this->model
                ->whereIn('id', $subscriptions->keys())
                ->update(['status' => SubscriptionStatus::Expired]);

            foreach ($subscriptions->values() as $userId) {
                $expiredUserIds[] = $userId;
            }
        } while ($subscriptions->count() === 500);

        return array_unique($expiredUserIds);
    }
}
