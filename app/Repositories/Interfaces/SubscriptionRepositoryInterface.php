<?php

namespace App\Repositories\Interfaces;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface
{
    /**
     * Cek apakah user memiliki langganan yang valid untuk mengunduh konten.
     *
     * Logika validasi:
     * - Status harus 'active'
     * - Tanggal hari ini (now) harus berada di antara started_at dan expires_at
     * - Jika expires_at NULL, dianggap lifetime (selalu valid selama status active)
     *
     * @param  int  $userId  ID pengguna yang akan dicek
     * @return bool          True jika boleh mengunduh, false jika tidak
     */
    public function isValidForDownload(int $userId): bool;

    /**
     * Temukan langganan aktif milik user tertentu.
     *
     * @param  int               $userId
     * @return Subscription|null  Null jika tidak ada langganan aktif
     */
    public function findActiveByUser(int $userId): ?Subscription;

    /**
     * Ambil seluruh riwayat langganan milik user.
     *
     * @param  int         $userId
     * @return Collection
     */
    public function getAllByUser(int $userId): Collection;

    /**
     * Buat langganan baru.
     *
     * @param  array<string, mixed>  $data  Data langganan (user_id, plan, started_at, expires_at, dsb.)
     * @return Subscription
     */
    public function create(array $data): Subscription;

    /**
     * Update status langganan berdasarkan ID-nya.
     *
     * @param  int                 $subscriptionId
     * @param  SubscriptionStatus  $status          Status baru yang akan diterapkan
     * @return bool                                 True jika berhasil diupdate
     */
    public function updateStatus(int $subscriptionId, SubscriptionStatus $status): bool;

    /**
     * Perpanjang masa berlaku langganan.
     *
     * @param  int     $subscriptionId
     * @param  int     $days            Jumlah hari perpanjangan
     * @param  string  $plan            Nama paket baru (opsional)
     * @return bool
     */
    public function extend(int $subscriptionId, int $days, ?string $plan = null): bool;

    /**
     * Tandai semua langganan yang sudah melewati expires_at sebagai 'expired'.
     * Digunakan oleh Scheduled Command yang berjalan setiap hari.
     *
     * @return int  Jumlah baris yang diupdate
     */
    public function expireOverdue(): int;
}
