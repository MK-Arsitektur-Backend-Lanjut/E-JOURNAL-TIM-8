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
            // Composite index to speed up per-user active-subscription checks
            $table->index(['user_id', 'status', 'started_at', 'expires_at'], 'idx_subscriptions_user_status_started_expires');

            // Index to accelerate batch expiration queries (WHERE status = ? AND expires_at < ?)
            $table->index(['status', 'expires_at'], 'idx_subscriptions_status_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_user_status_started_expires');
            $table->dropIndex('idx_subscriptions_status_expires_at');
        });
    }
};
