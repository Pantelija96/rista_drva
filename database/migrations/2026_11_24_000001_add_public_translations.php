<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
 Schema::table('schools', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('school_generations', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('tree_species', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('trees', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('tree_groups', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('gallery_albums', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('gallery_images', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('site_statistics', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('tree_maintenance_records', fn (Blueprint $table) => $table->json('translations')->nullable());
 Schema::table('school_applications', fn (Blueprint $table) => $table->string('preferred_locale',2)->default('sr'));
 Schema::table('tree_adoptions', fn (Blueprint $table) => $table->string('preferred_locale',2)->default('sr'));
 Schema::table('inquiries', fn (Blueprint $table) => $table->string('preferred_locale',2)->default('sr'));
 Schema::table('mail_outbox', fn (Blueprint $table) => $table->string('locale',2)->default('sr'));
 }
 public function down(): void { throw new \RuntimeException('Restore a verified backup; automatic destructive rollback is disabled.'); }
};
