<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'version')) {
                $table->string('version', 40)->nullable()->after('tags');
            }
            if (! Schema::hasColumn('items', 'changelog')) {
                $table->json('changelog')->nullable()->after('version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'changelog')) {
                $table->dropColumn('changelog');
            }
            if (Schema::hasColumn('items', 'version')) {
                $table->dropColumn('version');
            }
        });
    }
};
