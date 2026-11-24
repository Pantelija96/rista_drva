<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->string('kind');
            $t->string('name');
            $t->text('email')->nullable();
            $t->text('phone')->nullable();
            $t->string('organization')->nullable();
            $t->string('topic');
            $t->text('message');
            $t->string('status')->default('pending')->index();
            $t->text('internal_notes')->nullable();
            $t->string('submission_key', 64)->unique();
            $t->string('fingerprint', 64)->unique();
            $t->string('consent_version');
            $t->timestamp('consented_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
