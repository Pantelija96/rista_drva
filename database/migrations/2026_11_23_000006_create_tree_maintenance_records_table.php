<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_maintenance_records', function (Blueprint $t) {
            $t->id();
            $t->uuid('public_id')->unique();
            $t->foreignId('tree_id')->constrained()->restrictOnDelete();
            $t->foreignId('tree_adoption_id')->nullable()->constrained()->restrictOnDelete();
            $t->string('activity_type');
            $t->text('description');
            $t->text('public_description')->nullable();
            $t->date('performed_at');
            $t->string('submitted_by_type');
            $t->foreignId('submitted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('review_status')->default('pending')->index();
            $t->text('internal_notes')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->string('submission_key', 64)->nullable()->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Restore a verified backup through the documented recovery procedure.');
    }
};
