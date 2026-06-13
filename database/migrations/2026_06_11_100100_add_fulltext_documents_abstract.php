<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL for reliable fulltext creation across MySQL/MariaDB versions
        DB::statement('ALTER TABLE `documents` ADD FULLTEXT `ft_documents_abstract` (`abstract`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `documents` DROP INDEX `ft_documents_abstract`');
    }
};
