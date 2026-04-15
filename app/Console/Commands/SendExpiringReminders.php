<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Command;

/**
 * Command untuk mengirim email reminder ke user yang langganannya
 * akan kadaluarsa dalam N hari ke depan.
 *
 * Cara pakai:
 *   php artisan subscriptions:send-reminders         → reminder H-7 (default)
 *   php artisan subscriptions:send-reminders --days=3 → reminder H-3
 */
class SendExpiringReminders extends Command
{
    protected $signature   = 'subscriptions:send-reminders {--days=7 : Jumlah hari sebelum kadaluarsa}';
    protected $description = 'Kirim email reminder ke user yang langganannya akan kadaluarsa.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $this->info("📧 Mencari langganan yang akan kadaluarsa dalam {$days} hari...");

        // Ambil semua langganan yang akan expire dalam $days hari
        // Menggunakan scope scopeExpiringSoon() dari Model Subscription
        $subscriptions = Subscription::expiringSoon($days)
            ->with('user') // eager load agar tidak N+1 query
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('Tidak ada langganan yang akan kadaluarsa. Tidak ada email yang dikirim.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$subscriptions->count()} langganan. Mengirim reminder...");

        $sent   = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Kirim notifikasi ke user pemilik langganan
                // Karena ShouldQueue, ini hanya mendorong ke queue — tidak langsung kirim
                $subscription->user->notify(
                    new SubscriptionExpiringSoon($subscription)
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Gagal kirim ke user ID {$subscription->user_id}: {$e->getMessage()}");
            }
        }

        // Tampilkan ringkasan
        $this->newLine();
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total langganan ditemukan', $subscriptions->count()],
                ['✅ Email berhasil di-queue',  $sent],
                ['❌ Gagal',                    $failed],
            ]
        );

        $this->info('✅ Proses selesai. Email dikirim via queue worker.');

        return Command::SUCCESS;
    }
}
