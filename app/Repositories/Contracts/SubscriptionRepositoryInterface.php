<?php

namespace App\Repositories\Contracts;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface
{
    public function isValidForDownload(int $userId): bool;
    
    public function findActiveByUser(int $userId): ?Subscription;
    
    public function getAllByUser(int $userId): Collection;
    
    public function create(array $data): Subscription;
    
    public function updateStatus(int $subscriptionId, SubscriptionStatus $status): bool;
    
    public function extend(int $subscriptionId, int $days, ?string $plan = null): bool;
    
    public function expireOverdue(): int;
}
