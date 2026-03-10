<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('admin_menu_items', function (Blueprint $table): void {
            $table->string('section', 100)->nullable()->after('children_display');
        });

        Schema::table('admin_menu_items_translations', function (Blueprint $table): void {
            $table->string('section', 100)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('admin_menu_items', function (Blueprint $table): void {
            $table->dropColumn('section');
        });

        Schema::table('admin_menu_items_translations', function (Blueprint $table): void {
            $table->dropColumn('section');
        });
    }
};
