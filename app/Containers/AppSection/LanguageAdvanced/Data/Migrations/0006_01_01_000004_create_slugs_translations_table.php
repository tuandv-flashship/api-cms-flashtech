<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('slugs_translations')) {
            return;
        }

        Schema::create('slugs_translations', function (Blueprint $table): void {
            $table->string('lang_code', 20);
            $table->unsignedBigInteger('slugs_id');
            $table->string('key')->nullable();
            $table->string('prefix', 120)->nullable()->default('');

            $table->primary(['lang_code', 'slugs_id'], 'slugs_translations_primary');
            $table->index(['slugs_id', 'key', 'prefix'], 'slugs_translations_slugid_key_prefix_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slugs_translations');
    }
};
