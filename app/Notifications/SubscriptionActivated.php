<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi konfirmasi yang dikirim saat langganan user
 * berhasil diaktifkan (status berubah ke 'active').
 */
class SubscriptionActivated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan      = ucfirst($this->subscription->plan);
        $startedAt = $this->subscription->started_at->translatedFormat('d F Y');
        $expiresAt = $this->subscription->expires_at
            ? $this->subscription->expires_at->translatedFormat('d F Y')
            : 'Tidak terbatas (Lifetime)';

        return (new MailMessage)
            ->subject('✅ Langganan E-Journal Anda Berhasil Diaktifkan!')
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Langganan **{$plan}** Anda telah berhasil diaktifkan.")
            ->line('**Detail Langganan:**')
            ->line("- Paket     : {$plan}")
            ->line("- Mulai     : {$startedAt}")
            ->line("- Berakhir  : {$expiresAt}")
            ->action('Mulai Jelajahi E-Journal', url('/'))
            ->line('Terima kasih telah berlangganan. Selamat mengakses ribuan jurnal ilmiah!')
            ->salutation('Salam, Tim E-Journal');
    }
}
