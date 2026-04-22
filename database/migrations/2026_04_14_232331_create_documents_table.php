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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('author')->index();
            $table->integer('year')->index();
            $table->text('abstract')->nullable(); // Since filtering abstract with LIKE is slow, maybe SQLite FTS is better, but simple is fine for now
            $table->json('tags')->nullable();
            $table->string('file_path')->nullable(); // path to downloadable asset
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
