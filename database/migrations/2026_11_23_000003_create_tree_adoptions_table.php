<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_adoptions', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->foreignId('tree_id')->constrained()->restrictOnDelete();
            $t->foreignId('reserved_tree_id')->nullable()->unique()->constrained('trees')->restrictOnDelete();
            $t->text('guardian_name');
            $t->string('guardian_name_hash', 64)->index();
            $t->text('guardian_email')->nullable();
            $t->string('guardian_email_hash', 64)->index();
            $t->text('guardian_phone')->nullable();
            $t->string('adoption_type');
            $t->string('public_display_preference')->default('anonymous');
            $t->string('status')->default('pending_verification')->index();
            $t->string('verification_token_hash', 64)->nullable()->unique();
            $t->unsignedInteger('verification_version')->default(1);
            $t->timestamp('verification_expires_at');
            $t->timestamp('reservation_expires_at')->nullable()->index();
            $t->timestamp('email_verified_at')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('rejected_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamp('ended_at')->nullable();
            $t->string('consent_version');
            $t->timestamp('consented_at');
            $t->unsignedInteger('access_version')->default(1);
            $t->text('internal_notes')->nullable();
            $t->string('submission_key', 64)->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
