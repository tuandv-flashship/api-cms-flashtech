<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('custom_fields_translations')) {
            return;
        }

        Schema::create('custom_fields_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->unsignedBigInteger('custom_fields_id');
            $table->text('value')->nullable();

            $table->primary(['lang_code', 'custom_fields_id'], 'custom_fields_translations_primary');
            $table->index(['custom_fields_id', 'lang_code'], 'custom_fields_translations_cf_lang_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields_translations');
    }
};
