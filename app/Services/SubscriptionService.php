<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionActivated;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;

/**
 * SubscriptionService — Pusat semua business logic langganan.
 *
 * Controller hanya memanggil service ini.
 * Service tidak tahu tentang HTTP Request/Response — hanya business rules.
 *
 * Tanggung jawab:
 * - Menentukan skenario subscribe (baru / extend / tolak)
 * - Membuat, membatalkan, dan memperpanjang langganan
 * - Mengirim notifikasi ke user
 */
class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $repository
    ) {}

    /**
     * Proses permintaan berlangganan dengan 3 skenario:
     *
     * 1. Tidak ada langganan aktif    → buat baru
     * 2. Paket SAMA yang masih aktif  → extend otomatis
     * 3. Paket BERBEDA yang aktif     → tolak, minta cancel dulu
     *
     * @return array{status: string, subscription: Subscription, message: string}
     */
    public function subscribe(User $user, string $plan): array
    {
        $duration = config("plans.durations.{$plan}");
        $existing = $this->repository->findActiveByUser($user->id);

        // Skenario 2: Paket sama → extend otomatis
        if ($existing && $existing->plan === $plan) {
            $this->repository->extend($existing->id, $duration);
            $existing->refresh();

            return [
                'status'       => 'extended',
                'subscription' => $existing,
                'message'      => "Langganan {$plan} berhasil diperpanjang secara otomatis.",
            ];
        }

        // Skenario 3: Paket berbeda → tolak
        if ($existing) {
            return [
                'status'       => 'conflict',
                'subscription' => $existing,
                'message'      => "Anda masih memiliki langganan paket '{$existing->plan}' yang aktif (sisa {$existing->remainingDays()} hari). Batalkan terlebih dahulu sebelum berganti ke paket '{$plan}'.",
            ];
        }

        // Skenario 1: Buat baru
        $subscription = $this->repository->create([
            'user_id'    => $user->id,
            'plan'       => $plan,
            'started_at' => now(),
            'expires_at' => $duration ? now()->addDays($duration) : null,
            'status'     => SubscriptionStatus::Active,
        ]);

        // Kirim email konfirmasi via queue (tidak memblokir response)
        $user->notify(new SubscriptionActivated($subscription));

        return [
            'status'       => 'created',
            'subscription' => $subscription,
            'message'      => 'Langganan berhasil dibuat.',
        ];
    }

    /**
     * Batalkan langganan.
     * Guard: tidak bisa cancel yang sudah tidak aktif.
     *
     * @return array{success: bool, message: string}
     */
    public function cancel(Subscription $subscription): array
    {
        if (! $subscription->isActive()) {
            return [
                'success' => false,
                'message' => 'Langganan ini sudah tidak aktif, tidak perlu dibatalkan.',
            ];
        }

        $this->repository->updateStatus($subscription->id, SubscriptionStatus::Cancelled);

        return [
            'success' => true,
            'message' => 'Langganan berhasil dibatalkan.',
        ];
    }

    /**
     * Perpanjang langganan dengan jumlah hari tertentu.
     * Guard: tidak bisa extend yang tidak aktif.
     *
     * @return array{success: bool, subscription: ?Subscription, message: string}
     */
    public function extend(Subscription $subscription, int $days): array
    {
        if (! $subscription->isActive()) {
            return [
                'success'      => false,
                'subscription' => null,
                'message'      => 'Hanya langganan aktif yang bisa diperpanjang. Buat langganan baru melalui POST /api/membership/subscribe.',
            ];
        }

        $this->repository->extend($subscription->id, $days);
        $subscription->refresh();

        return [
            'success'      => true,
            'subscription' => $subscription,
            'message'      => "Langganan berhasil diperpanjang {$days} hari.",
        ];
    }
}
