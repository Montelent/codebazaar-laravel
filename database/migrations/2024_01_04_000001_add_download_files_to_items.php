<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('items')) {
            return;
        }

        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'download_files')) {
                $table->json('download_files')->nullable()->after('main_file_url');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'download_files')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('download_files');
            });
        }
    }
};
