<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 *
 * Factory ini mendukung beberapa "state" untuk skenario testing:
 * - active()    → langganan yang sedang berjalan (boleh unduh)
 * - expired()   → langganan yang sudah kadaluarsa (tidak boleh unduh)
 * - cancelled() → langganan yang dibatalkan user
 * - pending()   → menunggu aktivasi/pembayaran
 * - lifetime()  → langganan tanpa batas waktu
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * State default: menghasilkan langganan AKTIF (monthly).
     * Cocok untuk skenario "user boleh unduh".
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Default: langganan bulanan yang sedang aktif
        $startedAt  = $this->faker->dateTimeBetween('-25 days', '-1 day');
        $expiresAt  = (clone $startedAt)->modify('+30 days');

        return [
            'user_id'    => User::factory(),
            'plan'       => 'monthly',
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'status'     => SubscriptionStatus::Active,
            'notes'      => null,
        ];
    }

    // =========================================================================
    // STATES — Digunakan untuk skenario testing yang berbeda
    // =========================================================================

    /**
     * State: Langganan AKTIF dan masih dalam rentang berlaku.
     * → isValidForDownload() akan return TRUE
     *
     * Contoh: SubscriptionFactory::new()->active()->create()
     */
    public function active(): static
    {
        return $this->state(function () {
            $plan = $this->faker->randomElement(['monthly', 'yearly']);
            $days = $plan === 'yearly' ? 365 : 30;

            // started_at di masa lalu, expires_at di masa depan
            $startedAt = $this->faker->dateTimeBetween('-' . ($days - 1) . ' days', 'now');
            $expiresAt = (clone $startedAt)->modify("+{$days} days");

            return [
                'plan'       => $plan,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Active,
            ];
        });
    }

    /**
     * State: Langganan EXPIRED — sudah melewati expires_at.
     * → isValidForDownload() akan return FALSE
     *
     * Contoh: SubscriptionFactory::new()->expired()->create()
     */
    public function expired(): static
    {
        return $this->state(function () {
            $plan = $this->faker->randomElement(['monthly', 'yearly']);
            $days = $plan === 'yearly' ? 365 : 30;

            // Kedua tanggal di masa lalu — sudah berakhir
            $startedAt = $this->faker->dateTimeBetween('-2 years', '-' . ($days + 1) . ' days');
            $expiresAt = (clone $startedAt)->modify("+{$days} days");

            return [
                'plan'       => $plan,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Expired,
            ];
        });
    }

    /**
     * State: Langganan DIBATALKAN sebelum masa berakhir.
     * → isValidForDownload() akan return FALSE
     */
    public function cancelled(): static
    {
        return $this->state(function () {
            $startedAt = $this->faker->dateTimeBetween('-60 days', '-10 days');
            $expiresAt = (clone $startedAt)->modify('+30 days');

            return [
                'plan'       => 'monthly',
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Cancelled,
                'notes'      => $this->faker->randomElement([
                    'Dibatalkan oleh pengguna.',
                    'Pembayaran gagal.',
                    'Permintaan refund.',
                    null,
                ]),
            ];
        });
    }

    /**
     * State: Langganan PENDING — belum diaktivasi.
     * → isValidForDownload() akan return FALSE
     */
    public function pending(): static
    {
        return $this->state(function () {
            $startedAt = now();
            $expiresAt = now()->addDays(30);

            return [
                'plan'       => $this->faker->randomElement(['monthly', 'yearly']),
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Pending,
                'notes'      => 'Menunggu konfirmasi pembayaran.',
            ];
        });
    }

    /**
     * State: Langganan LIFETIME — tidak ada batas waktu (expires_at = NULL).
     * → isValidForDownload() akan return TRUE selama status active
     */
    public function lifetime(): static
    {
        return $this->state(function () {
            return [
                'plan'       => 'lifetime',
                'started_at' => $this->faker->dateTimeBetween('-3 years', '-1 month'),
                'expires_at' => null, // NULL = tidak ada batas waktu
                'status'     => SubscriptionStatus::Active,
            ];
        });
    }

    /**
     * State: Langganan paket YEARLY yang masih aktif.
     */
    public function yearly(): static
    {
        return $this->state(function () {
            $startedAt = $this->faker->dateTimeBetween('-364 days', '-1 day');
            $expiresAt = (clone $startedAt)->modify('+365 days');

            return [
                'plan'       => 'yearly',
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Active,
            ];
        });
    }

    /**
     * State: Langganan TRIAL singkat (7 hari).
     */
    public function trial(): static
    {
        return $this->state(function () {
            $startedAt = $this->faker->dateTimeBetween('-6 days', 'now');
            $expiresAt = (clone $startedAt)->modify('+7 days');

            return [
                'plan'       => 'trial',
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status'     => SubscriptionStatus::Active,
            ];
        });
    }
}
