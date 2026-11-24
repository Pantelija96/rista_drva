<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_applications', function (Blueprint $t) {
            $t->id();
            $t->string('school_name');
            $t->string('city');
            $t->string('municipality')->nullable();
            $t->string('contact_person');
            $t->string('contact_role')->nullable();
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('desired_planting_model');
            $t->unsignedInteger('estimated_graduate_count')->nullable();
            $t->text('message')->nullable();
            $t->boolean('privacy_consent');
            $t->timestamp('consent_timestamp');
            $t->string('submission_key', 64)->unique();
            $t->string('active_fingerprint', 64)->nullable()->unique();
            $t->string('status')->default('pending')->index();
            $t->text('internal_notes')->nullable();
            $t->foreignId('school_id')->nullable()->constrained()->restrictOnDelete();
            $t->timestamp('notification_sent_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback is disabled. Restore a verified backup through the documented recovery procedure.');
    }
};
