<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Index for ORDER BY views DESC (blog report top viewed posts)
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                if (! Schema::hasIndex('posts', 'posts_views_desc_index')) {
                    $table->index('views', 'posts_views_desc_index');
                }

                if (! Schema::hasIndex('posts', 'posts_is_featured_index')) {
                    $table->index('is_featured', 'posts_is_featured_index');
                }
            });
        }

        // Index for tags status filter
        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table): void {
                if (! Schema::hasIndex('tags', 'tags_status_index')) {
                    $table->index('status', 'tags_status_index');
                }
            });
        }

        // Index for slugables lookup (polymorphic slug resolution)
        if (Schema::hasTable('slugs')) {
            Schema::table('slugs', function (Blueprint $table): void {
                if (! Schema::hasIndex('slugs', 'slugs_reference_type_id_index')) {
                    $table->index(['reference_type', 'reference_id'], 'slugs_reference_type_id_index');
                }
            });
        }

        // Composite index for pages listing (status + created_at ordering)
        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table): void {
                if (! Schema::hasIndex('pages', 'pages_status_created_index')) {
                    $table->index(['status', 'created_at'], 'pages_status_created_index');
                }
            });
        }

        // Composite index for galleries listing (status + created_at ordering)
        if (Schema::hasTable('galleries')) {
            Schema::table('galleries', function (Blueprint $table): void {
                if (! Schema::hasIndex('galleries', 'galleries_status_created_index')) {
                    $table->index(['status', 'created_at'], 'galleries_status_created_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                if (Schema::hasIndex('posts', 'posts_views_desc_index')) {
                    $table->dropIndex('posts_views_desc_index');
                }
                if (Schema::hasIndex('posts', 'posts_is_featured_index')) {
                    $table->dropIndex('posts_is_featured_index');
                }
            });
        }

        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table): void {
                if (Schema::hasIndex('tags', 'tags_status_index')) {
                    $table->dropIndex('tags_status_index');
                }
            });
        }

        if (Schema::hasTable('slugs')) {
            Schema::table('slugs', function (Blueprint $table): void {
                if (Schema::hasIndex('slugs', 'slugs_reference_type_id_index')) {
                    $table->dropIndex('slugs_reference_type_id_index');
                }
            });
        }

        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table): void {
                if (Schema::hasIndex('pages', 'pages_status_created_index')) {
                    $table->dropIndex('pages_status_created_index');
                }
            });
        }

        if (Schema::hasTable('galleries')) {
            Schema::table('galleries', function (Blueprint $table): void {
                if (Schema::hasIndex('galleries', 'galleries_status_created_index')) {
                    $table->dropIndex('galleries_status_created_index');
                }
            });
        }
    }
};
