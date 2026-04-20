<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang boleh diisi secara massal (mass assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan',
        'started_at',
        'expires_at',
        'status',
        'notes',
    ];

    /**
     * Cast otomatis untuk tipe data yang lebih kaya.
     * - Timestamp di-cast ke Carbon agar mudah dihitung durasi.
     * - Status di-cast ke Enum SubscriptionStatus (type-safe).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'status'     => SubscriptionStatus::class,
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Setiap langganan dimiliki oleh satu pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // SCOPES — untuk menyederhakan query yang sering dipakai
    // =========================================================================

    /**
     * Scope: hanya langganan yang berstatus aktif.
     *
     * Contoh: Subscription::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    /**
     * Scope: hanya langganan yang sudah kadaluarsa.
     *
     * Contoh: Subscription::expired()->get();
     */
    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::Expired);
    }

    /**
     * Scope: langganan yang akan kadaluarsa dalam N hari ke depan.
     * Berguna untuk mengirim notifikasi pengingat perpanjangan.
     *
     * Contoh: Subscription::expiringSoon(7)->get();
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', SubscriptionStatus::Active)
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    // =========================================================================
    // ACCESSORS / COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Mengecek apakah langganan ini masih aktif secara real-time.
     *
     * Logika:
     * - Status harus 'active'
     * - Jika expires_at NULL, dianggap langganan seumur hidup (lifetime)
     * - Jika expires_at ada, pastikan belum terlewati
     */
    public function isActive(): bool
    {
        if ($this->status !== SubscriptionStatus::Active) {
            return false;
        }

        // Lifetime subscription (expires_at = null)
        if (is_null($this->expires_at)) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Accessor: Apakah langganan ini bersifat lifetime (expires_at = null)?
     *
     * Digunakan di view sebagai: $subscription->is_lifetime
     */
    protected function isLifetime(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->expires_at)
        );
    }

    /**
     * Accessor: Sisa hari langganan.
     *
     * Digunakan di view sebagai: $subscription->remaining_days
     * Mengembalikan:
     * - NULL : lifetime (tidak berbatas waktu)
     * - 0    : sudah kadaluarsa
     * - N    : jumlah hari tersisa (pembulatan ke atas)
     */
    protected function remainingDays(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (is_null($this->expires_at)) {
                    return null; // lifetime
                }

                if ($this->expires_at->isPast()) {
                    return 0;
                }

                return (int) ceil(now()->floatDiffInDays($this->expires_at));
            }
        );
    }

    /**
     * Menghitung total durasi langganan dalam hari.
     *
     * Berguna untuk laporan statistik berapa lama user berlangganan.
     */
    public function durationInDays(): ?int
    {
        if (is_null($this->expires_at)) {
            return null; // lifetime, tidak bisa dihitung
        }

        return (int) $this->started_at->diffInDays($this->expires_at);
    }

    // =========================================================================
    // BUSINESS LOGIC METHODS
    // =========================================================================

    /**
     * Mengaktifkan langganan.
     *
     * Digunakan setelah konfirmasi pembayaran berhasil.
     * Status berubah dari 'pending' → 'active'.
     */
    public function activate(): bool
    {
        return $this->update([
            'status'     => SubscriptionStatus::Active,
            'started_at' => now(),
        ]);
    }

    /**
     * Membatalkan langganan sebelum masa berakhir.
     *
     * @param string|null $reason  Alasan pembatalan (opsional, disimpan di notes)
     */
    public function cancel(?string $reason = null): bool
    {
        return $this->update([
            'status' => SubscriptionStatus::Cancelled,
            'notes'  => $reason,
        ]);
    }

    /**
     * Menandai langganan sebagai kadaluarsa.
     *
     * Dipanggil oleh Scheduled Command / Job yang berjalan setiap malam
     * untuk memperbarui status langganan yang sudah melewati expires_at.
     *
     * Contoh: php artisan subscriptions:expire
     */
    public function markAsExpired(): bool
    {
        return $this->update(['status' => SubscriptionStatus::Expired]);
    }

    /**
     * Memperpanjang langganan dari tanggal kadaluarsa saat ini.
     *
     * Logika perpanjangan:
     * - Jika masih aktif  : perpanjang dari expires_at saat ini
     * - Jika sudah expired: perpanjang dari sekarang (now)
     *
     * @param int    $days   Jumlah hari perpanjangan
     * @param string $plan   Paket baru (opsional, default pakai paket lama)
     */
    public function extend(int $days, ?string $plan = null): bool
    {
        $base = ($this->expires_at && $this->expires_at->isFuture())
            ? $this->expires_at
            : now();

        return $this->update([
            'status'     => SubscriptionStatus::Active,
            'expires_at' => $base->addDays($days),
            'plan'       => $plan ?? $this->plan,
        ]);
    }
}
