<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan indeks untuk optimasi query performance
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Indeks compound untuk filtering umum (author_id + year)
            $table->index(['author_id', 'year'], 'idx_documents_author_year');
            
            // Indeks untuk sorting latest
            $table->index('created_at', 'idx_documents_created_at');
        });

        Schema::table('document_tag', function (Blueprint $table) {
            // Indeks individual untuk filtering by tag
            $table->index('tag_id', 'idx_document_tag_tag_id');
        });

        Schema::table('tags', function (Blueprint $table) {
            // Indeks untuk search by name (exact match lebih baik dari LIKE)
            $table->index('name', 'idx_tags_name');
        });

        Schema::table('authors', function (Blueprint $table) {
            // Indeks compound untuk filtering
            $table->index('created_at', 'idx_authors_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_documents_author_year');
            $table->dropIndex('idx_documents_created_at');
        });

        Schema::table('document_tag', function (Blueprint $table) {
            $table->dropIndex('idx_document_tag_tag_id');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('idx_tags_name');
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropIndex('idx_authors_created_at');
        });
    }
};
