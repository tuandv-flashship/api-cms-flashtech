<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 20);
            $table->string('group_key', 100)->default('*');
            $table->string('item_key', 255);
            $table->text('value');
            $table->timestamps();

            $table->unique(['locale', 'group_key', 'item_key'], 'idx_translation_unique');
            $table->index(['locale', 'group_key'], 'idx_translation_locale_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
