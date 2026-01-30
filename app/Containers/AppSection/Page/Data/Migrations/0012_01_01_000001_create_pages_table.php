<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pages')) {
            return;
        }

        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('image')->nullable();
            $table->string('template', 60)->nullable();
            $table->string('description', 400)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();

            $table->index(['status', 'user_id', 'created_at'], 'pages_status_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
