<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('menu_nodes_translations')) {
            return;
        }

        Schema::create('menu_nodes_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->unsignedBigInteger('menu_nodes_id');
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->primary(['lang_code', 'menu_nodes_id'], 'menu_nodes_translations_primary');
            $table->foreign('menu_nodes_id')
                ->references('id')
                ->on('menu_nodes')
                ->cascadeOnDelete();
            $table->index(['menu_nodes_id', 'lang_code'], 'menu_nodes_translations_node_lang_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_nodes_translations');
    }
};
