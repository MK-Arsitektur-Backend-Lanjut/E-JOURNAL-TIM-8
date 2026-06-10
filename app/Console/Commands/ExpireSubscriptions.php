<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'Tandai langganan yang sudah melewati expires_at sebagai expired.';

    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $expiredUsers = $this->subscriptionRepository->expireOverdue();
        $count = count($expiredUsers);

        $this->info("✅ {$count} langganan berhasil ditandai sebagai expired.");

        return Command::SUCCESS;
    }
}
