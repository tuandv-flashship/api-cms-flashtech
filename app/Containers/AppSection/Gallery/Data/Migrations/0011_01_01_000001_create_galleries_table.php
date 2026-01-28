<?php

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('galleries')) {
            Schema::create('galleries', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120);
                $table->longText('description');
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('order')->default(0);
                $table->string('image')->nullable();
                $table->string('status', 60)->default('published');
                $table->unsignedBigInteger('author_id')->nullable();
                $table->string('author_type')->default(addslashes(User::class));
                $table->timestamps();

                $table->index(['status', 'author_id', 'author_type', 'created_at'], 'galleries_status_author_created_index');
            });
        }

        if (! Schema::hasTable('gallery_meta')) {
            Schema::create('gallery_meta', function (Blueprint $table): void {
                $table->id();
                $table->text('images')->nullable();
                $table->unsignedBigInteger('reference_id')->index();
                $table->string('reference_type', 120);
                $table->timestamps();

                $table->index(['reference_type', 'reference_id'], 'gallery_meta_reference_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_meta');
        Schema::dropIfExists('galleries');
    }
};
