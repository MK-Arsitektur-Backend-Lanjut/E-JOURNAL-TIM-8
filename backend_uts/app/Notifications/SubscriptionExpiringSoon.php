<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi email yang dikirim saat langganan user
 * akan kadaluarsa dalam N hari ke depan (default: 7 hari).
 *
 * Mengimplementasikan ShouldQueue agar pengiriman email
 * tidak memblokir proses scheduler — dikirim via queue worker
 * di background (penting untuk performa di Docker).
 */
class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    /**
     * Channel pengiriman notifikasi.
     * Bisa ditambahkan 'database' jika ingin simpan ke tabel notifications.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Konten email yang dikirim ke user.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $remainingDays = $this->subscription->remainingDays();
        $expiresAt     = $this->subscription->expires_at->translatedFormat('d F Y');
        $plan          = ucfirst($this->subscription->plan);

        return (new MailMessage)
            ->subject("⏰ Reminder: Langganan E-Journal Anda Akan Berakhir dalam {$remainingDays} Hari")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Kami ingin menginformasikan bahwa langganan **{$plan}** Anda akan berakhir pada **{$expiresAt}** ({$remainingDays} hari lagi).")
            ->line('Perpanjang sekarang agar akses Anda ke seluruh konten E-Journal tidak terputus.')
            ->action('Perpanjang Langganan Sekarang', url('/membership/renew'))
            ->line('Jika Anda sudah memperpanjang, abaikan email ini.')
            ->salutation('Salam, Tim E-Journal');
    }
}
