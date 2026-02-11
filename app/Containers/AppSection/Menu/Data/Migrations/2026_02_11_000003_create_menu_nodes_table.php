<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('menu_nodes')) {
            return;
        }

        Schema::create('menu_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_nodes')->cascadeOnDelete();
            $table->string('reference_type', 150)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('url')->nullable();
            $table->string('title')->nullable();
            $table->string('url_source', 20)->default('custom');
            $table->string('title_source', 20)->default('custom');
            $table->string('icon_font', 120)->nullable();
            $table->string('css_class', 120)->nullable();
            $table->string('target', 20)->default('_self');
            $table->boolean('has_child')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id'], 'menu_nodes_menu_parent_index');
            $table->index(['reference_type', 'reference_id'], 'menu_nodes_reference_index');
            $table->index(['menu_id', 'position'], 'menu_nodes_menu_position_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_nodes');
    }
};
