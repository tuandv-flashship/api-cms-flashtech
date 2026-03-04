<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('admin_menu_items_translations')) {
            return;
        }

        Schema::create('admin_menu_items_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->unsignedBigInteger('admin_menu_items_id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->primary(['lang_code', 'admin_menu_items_id'], 'admin_menu_items_translations_primary');
            $table->foreign('admin_menu_items_id')
                ->references('id')
                ->on('admin_menu_items')
                ->cascadeOnDelete();
            $table->index(['admin_menu_items_id', 'lang_code'], 'admin_menu_items_trans_item_lang_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menu_items_translations');
    }
};
