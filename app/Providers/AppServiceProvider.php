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
use App\Repositories\Eloquent\EloquentSubscriptionRepository;
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
        // Repository binding
        $this->app->bind(
            SubscriptionRepositoryInterface::class,
            EloquentSubscriptionRepository::class,
        );

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
        // Daftarkan SubscriptionPolicy
        Gate::policy(Subscription::class, SubscriptionPolicy::class);

        // 🔄 Daftarkan Observers untuk automatic cache invalidation
        Document::observe(DocumentObserver::class);
        Author::observe(AuthorObserver::class);
        Tag::observe(TagObserver::class);
    }
}
