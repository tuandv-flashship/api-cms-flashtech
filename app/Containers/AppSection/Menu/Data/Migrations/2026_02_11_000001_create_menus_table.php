<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            return;
        }

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('status', 30)->default('published');
            $table->timestamps();

            $table->index(['status', 'created_at'], 'menus_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
