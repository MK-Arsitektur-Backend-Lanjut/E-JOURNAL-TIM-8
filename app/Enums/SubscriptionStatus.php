<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    /**
     * Langganan sedang aktif dan `expires_at` belum terlewati.
     */
    case Active = 'active';

    /**
     * Langganan telah melewati tanggal `expires_at`.
     * Ditandai otomatis oleh scheduled job atau saat dicek melalui model.
     */
    case Expired = 'expired';

    /**
     * Langganan dibatalkan secara manual oleh user atau admin
     * sebelum tanggal `expires_at`.
     */
    case Cancelled = 'cancelled';

    /**
     * Langganan baru dibuat namun belum diaktivasi
     * (menunggu konfirmasi pembayaran, dsb.).
     */
    case Pending = 'pending';

    /**
     * Mengembalikan label yang ramah untuk ditampilkan di UI.
     */
    public function label(): string
    {
        return match($this) {
            self::Active    => 'Aktif',
            self::Expired   => 'Kadaluarsa',
            self::Cancelled => 'Dibatalkan',
            self::Pending   => 'Menunggu Aktivasi',
        };
    }

    /**
     * Warna badge untuk tampilan UI (Tailwind / custom class).
     */
    public function color(): string
    {
        return match($this) {
            self::Active    => 'green',
            self::Expired   => 'red',
            self::Cancelled => 'gray',
            self::Pending   => 'yellow',
        };
    }
}
