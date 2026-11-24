<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_adoption_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tree_adoption_id')->constrained()->restrictOnDelete();
            $t->string('event');
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->string('actor_type');
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->json('metadata')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
