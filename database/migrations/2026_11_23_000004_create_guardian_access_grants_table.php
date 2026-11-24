<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_access_grants', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->foreignId('tree_adoption_id')->constrained()->restrictOnDelete();
            $t->string('token_hash', 64)->nullable()->unique();
            $t->timestamp('expires_at');
            $t->timestamp('consumed_at')->nullable();
            $t->unsignedInteger('access_version');
            $t->string('purpose')->default('access');
            $t->text('new_email')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
