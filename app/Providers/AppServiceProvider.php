<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Models\Document;
use App\Models\Author;
use App\Models\Tag;
use App\Observers\DocumentObserver;
use App\Observers\AuthorObserver;
use App\Observers\TagObserver;
use App\Policies\SubscriptionPolicy;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Services\AuthService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Decorator Pattern: Toggle antara cached vs direct repository
        // Dikontrol oleh env var SUBSCRIPTION_CACHE_ENABLED (config plans.cache_enabled)
        $this->app->singleton(SubscriptionRepositoryInterface::class, function ($app) {
            $eloquent = new \App\Repositories\Eloquent\EloquentSubscriptionRepository(
                new \App\Models\Subscription()
            );

            // Jika cache dinonaktifkan → langsung pakai Eloquent (untuk baseline benchmark)
            if (!config('plans.cache_enabled', true)) {
                return $eloquent;
            }

            // Jika cache diaktifkan → bungkus dengan CachedSubscriptionRepository (decorator)
            return new \App\Repositories\Eloquent\CachedSubscriptionRepository(
                $eloquent,
                $app->make(\Illuminate\Contracts\Cache\Repository::class)
            );
        });

        $this->app->bind(
            \App\Repositories\Contracts\DocumentRepositoryInterface::class,
            \App\Repositories\Eloquent\DocumentRepository::class,
        );

        // Service binding
        $this->app->singleton(SubscriptionService::class);
        $this->app->singleton(AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gunakan custom token model untuk menonaktifkan update last_used_at (menghindari write lock contention)
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        // Daftarkan SubscriptionPolicy
        Gate::policy(Subscription::class, SubscriptionPolicy::class);

        Document::observe(DocumentObserver::class);
        Author::observe(AuthorObserver::class);
        Tag::observe(TagObserver::class);
    }
}
