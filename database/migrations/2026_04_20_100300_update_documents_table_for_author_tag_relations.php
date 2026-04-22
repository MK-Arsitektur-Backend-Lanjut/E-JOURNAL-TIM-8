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
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('id')
                ->constrained('authors')
                ->nullOnDelete();

            $table->dropIndex(['author']);
            $table->dropColumn(['author', 'tags']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('author')->nullable()->index();
            $table->json('tags')->nullable();
            $table->dropConstrainedForeignId('author_id');
        });
    }
};
