<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menyimpan data langganan (subscription) pengguna
     * pada sistem Digital Library (E-Journal).
     *
     * Logika durasi langganan:
     * - `started_at`  : Tanggal & waktu langganan mulai aktif.
     * - `expires_at`  : Tanggal & waktu langganan berakhir. Dihitung
     *                   dari `started_at` + durasi paket (misal: 30 hari,
     *                   1 tahun). NULL berarti langganan tidak terbatas.
     * - `status`      : Enum yang merepresentasikan kondisi langganan saat ini.
     *                   Nilai dikelola melalui App\Enums\SubscriptionStatus.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel users (one user can have many subscriptions)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Tipe paket langganan (misal: 'monthly', 'yearly', 'trial')
            $table->string('plan')->default('monthly');

            // Waktu mulai langganan aktif
            $table->timestamp('started_at');

            // Waktu berakhir langganan. NULL = tidak ada batas waktu (lifetime)
            $table->timestamp('expires_at')->nullable();

            // Status langganan: active, expired, cancelled, pending
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])
                ->default('pending');

            // Catatan tambahan (alasan pembatalan, keterangan admin, dsb.)
            $table->text('notes')->nullable();

            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at untuk soft delete

            // Index untuk mempercepat query filtering berdasarkan status & masa aktif
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
