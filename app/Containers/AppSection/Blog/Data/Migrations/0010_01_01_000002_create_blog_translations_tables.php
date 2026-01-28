<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('posts_translations')) {
            Schema::create('posts_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('posts_id');
                $table->string('name')->nullable();
                $table->string('description', 400)->nullable();
                $table->longText('content')->nullable();

                $table->primary(['lang_code', 'posts_id'], 'posts_translations_primary');
                $table->index(['posts_id', 'lang_code'], 'posts_translations_post_lang_index');
            });
        }

        if (! Schema::hasTable('categories_translations')) {
            Schema::create('categories_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('categories_id');
                $table->string('name')->nullable();
                $table->string('description', 400)->nullable();

                $table->primary(['lang_code', 'categories_id'], 'categories_translations_primary');
                $table->index(['categories_id', 'lang_code'], 'categories_translations_category_lang_index');
            });
        }

        if (! Schema::hasTable('tags_translations')) {
            Schema::create('tags_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('tags_id');
                $table->string('name')->nullable();
                $table->string('description', 400)->nullable();

                $table->primary(['lang_code', 'tags_id'], 'tags_translations_primary');
                $table->index(['tags_id', 'lang_code'], 'tags_translations_tag_lang_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts_translations');
        Schema::dropIfExists('categories_translations');
        Schema::dropIfExists('tags_translations');
    }
};
