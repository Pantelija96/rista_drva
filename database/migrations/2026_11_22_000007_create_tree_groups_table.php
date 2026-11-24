<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->foreignId('school_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('school_generation_id')->nullable()->constrained()->restrictOnDelete();
            $t->text('description')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->date('planted_at')->nullable();
            $t->unsignedInteger('declared_tree_count')->nullable();
            $t->string('featured_image_path')->nullable();
            $t->boolean('published')->default(false)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
