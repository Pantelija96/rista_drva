<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trees', function (Blueprint $t) {
            $t->id();
            $t->string('public_identifier')->nullable()->unique();
            $t->foreignId('tree_species_id')->constrained('tree_species')->restrictOnDelete();
            $t->foreignId('school_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('school_generation_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('tree_group_id')->nullable()->constrained()->restrictOnDelete();
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->date('planted_at')->nullable();
            $t->string('physical_condition')->default('planned');
            $t->string('adoption_status')->default('unavailable')->index();
            $t->boolean('public_visibility')->default(false)->index();
            $t->string('featured_image_path')->nullable();
            $t->json('photo_paths')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
