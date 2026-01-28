<?php

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120);
                $table->unsignedBigInteger('parent_id')->default(0);
                $table->string('description', 400)->nullable();
                $table->string('status', 60)->default('published');
                $table->unsignedBigInteger('author_id')->nullable();
                $table->string('author_type')->default(addslashes(User::class));
                $table->string('icon', 60)->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['parent_id', 'status', 'created_at'], 'categories_parent_status_created_index');
            });
        }

        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120);
                $table->unsignedBigInteger('author_id')->nullable();
                $table->string('author_type')->default(addslashes(User::class));
                $table->string('description', 400)->nullable();
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('description', 400)->nullable();
                $table->longText('content')->nullable();
                $table->string('status', 60)->default('published');
                $table->unsignedBigInteger('author_id')->nullable();
                $table->string('author_type')->default(addslashes(User::class));
                $table->boolean('is_featured')->default(false);
                $table->string('image')->nullable();
                $table->unsignedInteger('views')->default(0);
                $table->string('format_type', 30)->nullable();
                $table->timestamps();

                $table->index(['status', 'author_id', 'author_type', 'created_at'], 'posts_status_author_created_index');
            });
        }

        if (! Schema::hasTable('post_tags')) {
            Schema::create('post_tags', function (Blueprint $table): void {
                $table->unsignedBigInteger('tag_id')->index();
                $table->unsignedBigInteger('post_id')->index();
            });
        }

        if (! Schema::hasTable('post_categories')) {
            Schema::create('post_categories', function (Blueprint $table): void {
                $table->unsignedBigInteger('category_id')->index();
                $table->unsignedBigInteger('post_id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tags');
    }
};
