<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_albums', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('cover_image_path')->nullable();
            $t->foreignId('school_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('school_generation_id')->nullable()->constrained()->restrictOnDelete();
            $t->date('event_date')->nullable();
            $t->string('location_label')->nullable();
            $t->string('category')->default('sadnje');
            $t->boolean('published')->default(false)->index();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
