<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (! Schema::hasColumn('items', 'seo_title')) {
                    $table->string('seo_title', 200)->nullable();
                }
                if (! Schema::hasColumn('items', 'seo_description')) {
                    $table->string('seo_description', 320)->nullable();
                }
                if (! Schema::hasColumn('items', 'seo_keywords')) {
                    $table->string('seo_keywords', 255)->nullable();
                }
                if (! Schema::hasColumn('items', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable();
                }
                if (! Schema::hasColumn('items', 'og_title')) {
                    $table->string('og_title', 200)->nullable();
                }
                if (! Schema::hasColumn('items', 'og_description')) {
                    $table->string('og_description', 320)->nullable();
                }
                if (! Schema::hasColumn('items', 'og_image')) {
                    $table->string('og_image', 1000)->nullable();
                }
                if (! Schema::hasColumn('items', 'robots')) {
                    $table->string('robots', 80)->nullable();
                }
                if (! Schema::hasColumn('items', 'focus_keyword')) {
                    $table->string('focus_keyword', 120)->nullable();
                }
            });
        }

        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                if (! Schema::hasColumn('blog_posts', 'seo_keywords')) {
                    $table->string('seo_keywords', 255)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'og_title')) {
                    $table->string('og_title', 200)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'og_description')) {
                    $table->string('og_description', 320)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'og_image')) {
                    $table->string('og_image', 1000)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'robots')) {
                    $table->string('robots', 80)->nullable();
                }
                if (! Schema::hasColumn('blog_posts', 'focus_keyword')) {
                    $table->string('focus_keyword', 120)->nullable();
                }
            });
        }

        if (Schema::hasTable('cms_pages')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                if (! Schema::hasColumn('cms_pages', 'seo_keywords')) {
                    $table->string('seo_keywords', 255)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'og_title')) {
                    $table->string('og_title', 200)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'og_description')) {
                    $table->string('og_description', 320)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'og_image')) {
                    $table->string('og_image', 1000)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'robots')) {
                    $table->string('robots', 80)->nullable();
                }
                if (! Schema::hasColumn('cms_pages', 'focus_keyword')) {
                    $table->string('focus_keyword', 120)->nullable();
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'seo_title')) {
                    $table->string('seo_title', 200)->nullable();
                }
                if (! Schema::hasColumn('categories', 'seo_description')) {
                    $table->string('seo_description', 320)->nullable();
                }
                if (! Schema::hasColumn('categories', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable();
                }
                if (! Schema::hasColumn('categories', 'robots')) {
                    $table->string('robots', 80)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Non-destructive: leave columns in place.
    }
};
