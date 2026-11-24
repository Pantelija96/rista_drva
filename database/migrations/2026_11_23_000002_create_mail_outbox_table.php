<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_outbox', function (Blueprint $t) {
            $t->id();
            $t->string('event');
            $t->string('reference');
            $t->string('dedup_key')->unique();
            $t->text('payload')->nullable();
            $t->string('status')->default('pending')->index();
            $t->unsignedInteger('attempts')->default(0);
            $t->timestamp('available_at')->index();
            $t->string('claim', 64)->nullable();
            $t->timestamp('locked_until')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->string('transport')->nullable();
            $t->string('last_error')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
