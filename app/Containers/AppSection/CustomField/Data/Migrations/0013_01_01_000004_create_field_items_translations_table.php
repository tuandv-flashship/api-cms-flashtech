<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('field_items_translations')) {
            Schema::create('field_items_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('field_items_id');
                $table->string('title')->nullable();
                $table->text('instructions')->nullable();

                $table->primary(['lang_code', 'field_items_id'], 'field_items_translations_primary');
                $table->index(['field_items_id', 'lang_code'], 'field_items_translations_fi_lang_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_items_translations');
    }
};
