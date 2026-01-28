<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('galleries_translations')) {
            Schema::create('galleries_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('galleries_id');
                $table->string('name')->nullable();
                $table->longText('description')->nullable();

                $table->primary(['lang_code', 'galleries_id'], 'galleries_translations_primary');
            });
        }

        if (! Schema::hasTable('gallery_meta_translations')) {
            Schema::create('gallery_meta_translations', function (Blueprint $table): void {
                $table->string('lang_code', 20);
                $table->unsignedBigInteger('gallery_meta_id');
                $table->text('images')->nullable();

                $table->primary(['lang_code', 'gallery_meta_id'], 'gallery_meta_translations_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_meta_translations');
        Schema::dropIfExists('galleries_translations');
    }
};
