<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stop on collisions. Inspect and adapt existing tables before applying this migration.
        foreach (['users', 'password_reset_tokens', 'schools', 'school_generations', 'tree_species', 'tree_groups', 'trees', 'school_applications', 'gallery_albums', 'gallery_images', 'site_statistics'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException("Table {$table} already exists. Review the schema and a verified backup before migration.");
            }
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
