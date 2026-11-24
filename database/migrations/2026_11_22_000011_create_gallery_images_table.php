<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gallery_album_id')->constrained()->restrictOnDelete();
            $t->string('image_path')->unique();
            $t->string('alt_text');
            $t->text('caption')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('published')->default(false)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
