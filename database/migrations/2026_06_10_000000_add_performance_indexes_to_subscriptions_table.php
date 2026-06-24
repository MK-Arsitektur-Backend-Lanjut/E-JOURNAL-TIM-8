<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus simple index user_id jika ada (dari rollback sebelumnya)
        // agar tidak redundan dengan composite index
        try {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex('idx_subscriptions_user_id_simple');
            });
        } catch (\Exception $e) {
            // Index tidak ada, tidak perlu di-drop — lanjutkan
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            // Index komposit untuk query validasi download & aktif langganan per user
            $table->index(['user_id', 'status', 'started_at', 'expires_at'], 'idx_subscriptions_access_check');

            // Index komposit untuk query batch status update & email reminder
            $table->index(['status', 'expires_at'], 'idx_subscriptions_expiry_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('subscriptions', function (Blueprint $table) {
                // Buat index sederhana pada user_id dulu, karena MySQL butuh index
                // untuk foreign key constraint. Tanpa ini, composite index yang
                // dimulai dengan user_id tidak bisa di-drop.
                $table->index('user_id', 'idx_subscriptions_user_id_simple');
            });
        } catch (\Exception $e) {
            // Index sudah ada atau foreign key index sudah mencakup user_id, abaikan
        }

        try {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex('idx_subscriptions_access_check');
                $table->dropIndex('idx_subscriptions_expiry_check');
            });
        } catch (\Exception $e) {
            // Index sudah di-drop, abaikan
        }
    }
};
