<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * User hanya boleh melihat langganan miliknya sendiri.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Hanya admin (atau pemilik) yang boleh membuat langganan.
     * Saat ini semua user yang login boleh berlangganan sendiri.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * User hanya boleh cancel langganan MILIKNYA SENDIRI.
     * Admin bisa cancel milik siapapun.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * User hanya boleh perpanjang langganan MILIKNYA SENDIRI.
     */
    public function extend(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }
}
