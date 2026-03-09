<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('field_groups_translations')) {
            Schema::create('field_groups_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('field_groups_id');
                $table->string('title')->nullable();

                $table->primary(['lang_code', 'field_groups_id'], 'field_groups_translations_primary');
                $table->index(['field_groups_id', 'lang_code'], 'field_groups_translations_fg_lang_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_groups_translations');
    }
};
