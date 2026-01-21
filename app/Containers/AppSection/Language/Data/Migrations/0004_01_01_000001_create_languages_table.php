<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id('lang_id');
            $table->string('lang_name', 120);
            $table->string('lang_locale', 20);
            $table->string('lang_code', 20)->unique();
            $table->string('lang_flag', 20)->nullable();
            $table->boolean('lang_is_default')->default(false);
            $table->integer('lang_order')->default(0);
            $table->boolean('lang_is_rtl')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
