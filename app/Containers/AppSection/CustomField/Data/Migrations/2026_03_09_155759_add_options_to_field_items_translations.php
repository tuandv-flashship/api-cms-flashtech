<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('field_items_translations', function (Blueprint $table): void {
            $table->text('options')->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('field_items_translations', function (Blueprint $table): void {
            $table->dropColumn('options');
        });
    }
};
