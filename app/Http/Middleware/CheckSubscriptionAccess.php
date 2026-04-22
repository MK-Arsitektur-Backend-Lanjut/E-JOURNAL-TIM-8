<?php

namespace App\Http\Middleware;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionAccess
{
    /**
     * Repository di-inject otomatis oleh Laravel Container.
     * Tidak perlu new EloquentSubscriptionRepository() secara manual.
     */
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * Validasi apakah user yang sedang login memiliki langganan aktif.
     *
     * Alur pengecekan:
     * 1. Pastikan user sudah login (authenticated)
     * 2. Gunakan repository untuk cek validitas langganan berdasarkan tanggal
     * 3. Jika tidak valid → tolak dengan 403 JSON
     * 4. Jika valid → teruskan request ke controller
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Langkah 1: Pastikan user sudah login
        if (! $request->user()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        // Langkah 2: Cek langganan menggunakan repository
        // isValidForDownload() memverifikasi:
        // - status = 'active'
        // - started_at <= sekarang
        // - expires_at IS NULL (lifetime) ATAU expires_at >= sekarang
        $hasValidSubscription = $this->subscriptionRepository
            ->isValidForDownload($request->user()->id);

        // Langkah 3: Tolak jika tidak memiliki langganan valid
        if (! $hasValidSubscription) {
            return response()->json([
                'status'        => 'error',
                'code'          => 403,
                'message'       => 'Konten ini hanya tersedia untuk pelanggan aktif.',
                'action'        => 'Pilih paket berlangganan di bawah untuk mendapatkan akses penuh.',
                'subscribe_url' => url('/api/membership/subscribe'),
                'plans'         => collect(config('plans.available'))
                    ->mapWithKeys(fn ($plan) => [
                        $plan => config("plans.details.{$plan}"),
                    ]),
            ], 403);
        }

        // Langkah 4: Langganan valid — teruskan ke controller
        return $next($request);
    }
}
