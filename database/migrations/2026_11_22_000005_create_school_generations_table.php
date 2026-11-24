<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_generations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete();
            $t->unsignedSmallInteger('graduation_year');
            $t->string('program')->default('general');
            $t->string('name')->nullable();
            $t->string('planting_model');
            $t->unsignedInteger('expected_graduates')->nullable();
            $t->text('notes')->nullable();
            $t->string('status')->default('draft')->index();
            $t->timestamps();
            $t->unique(['school_id', 'graduation_year', 'program']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
