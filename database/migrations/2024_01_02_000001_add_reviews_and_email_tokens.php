<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrade-only: safe no-ops if tables already exist from the main install migration.
 * Kept so older installs that update the ZIP can still add missing tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->unsignedInteger('helpful')->default(0);
                $table->timestamps();
                $table->unique(['item_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('email_verification_tokens')) {
            Schema::create('email_verification_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('newsletter_logs')) {
            Schema::create('newsletter_logs', function (Blueprint $table) {
                $table->id();
                $table->string('subject');
                $table->longText('body')->nullable();
                $table->unsignedInteger('recipients')->default(0);
                $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void {}
};
