<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_species', function (Blueprint $t) {
            $t->id();
            $t->string('common_name');
            $t->string('scientific_name')->nullable();
            $t->string('slug')->unique();
            $t->text('short_description')->nullable();
            $t->text('description')->nullable();
            $t->string('main_image_path')->nullable();
            $t->json('gallery_paths')->nullable();
            $t->boolean('native_to_serbia')->nullable();
            $t->string('deciduous_or_evergreen')->nullable();
            $t->decimal('typical_height_m', 7, 2)->nullable();
            $t->unsignedInteger('expected_lifespan_years')->nullable();
            foreach (['preferred_habitat', 'sunlight_requirements', 'water_requirements', 'planting_recommendations', 'maintenance_guidelines', 'ecological_benefits', 'interesting_facts'] as $field) {
                $t->text($field)->nullable();
            }
            $t->boolean('published')->default(false)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
