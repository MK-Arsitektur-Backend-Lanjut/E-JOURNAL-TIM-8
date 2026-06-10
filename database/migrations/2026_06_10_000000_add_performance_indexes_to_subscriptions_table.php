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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_access_check');
            $table->dropIndex('idx_subscriptions_expiry_check');
        });
    }
};
