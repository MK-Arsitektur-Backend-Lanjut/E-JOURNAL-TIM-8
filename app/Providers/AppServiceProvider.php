<?php

namespace App\Providers;

use App\Models\Subscription;
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
     *
     * Di sinilah "jembatan" antara Interface dan Implementasi didaftarkan.
     * Setiap kali Laravel Container melihat SubscriptionRepositoryInterface
     * di constructor manapun, ia akan otomatis meng-inject
     * EloquentSubscriptionRepository sebagai implementasinya.
     *
     * Manfaat: Jika suatu saat ingin ganti ke implementasi lain
     * (misal: CacheSubscriptionRepository), cukup ubah di SINI SAJA —
     * tidak perlu menyentuh Controller atau class lain sama sekali.
     */
    public function register(): void
    {
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan SubscriptionPolicy agar authorize() di Controller berfungsi
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
    }
}
