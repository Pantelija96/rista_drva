<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('institution_type')->default('secondary');
            $t->string('city');
            $t->string('municipality')->nullable();
            $t->string('address')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('contact_name')->nullable();
            $t->string('contact_email')->nullable();
            $t->string('contact_phone')->nullable();
            $t->text('description')->nullable();
            $t->string('logo_path')->nullable();
            $t->string('featured_image_path')->nullable();
            $t->string('status')->default('draft')->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
