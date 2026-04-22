<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
<<<<<<< Updated upstream
        //
=======
        // Repository binding — swap implementasi tanpa ubah controller/service
        $this->app->bind(
            SubscriptionRepositoryInterface::class,
            EloquentSubscriptionRepository::class,
        );

        $this->app->bind(
            \App\Repositories\Contracts\DocumentRepositoryInterface::class,
            \App\Repositories\Eloquent\DocumentRepository::class,
        );

        // Service binding — singleton agar tidak buat instance baru tiap request
        $this->app->singleton(SubscriptionService::class);
        $this->app->singleton(AuthService::class);
>>>>>>> Stashed changes
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
