<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_menu_items')) {
            return;
        }

        Schema::create('admin_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('admin_menu_items')->cascadeOnDelete();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->string('icon', 120)->nullable();
            $table->string('route')->nullable();
            $table->json('permissions')->nullable();
            $table->string('children_display', 20)->default('sidebar');
            $table->string('description')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['parent_id', 'priority'], 'admin_menu_items_parent_priority_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menu_items');
    }
};
