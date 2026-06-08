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
        Schema::table('document_tag', function (Blueprint $table) {
            $table->dropIndex('idx_document_tag_tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_tag', function (Blueprint $table) {
            $table->index('tag_id', 'idx_document_tag_tag_id');
        });
    }
};
