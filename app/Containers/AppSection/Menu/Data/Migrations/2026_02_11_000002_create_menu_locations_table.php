<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('menu_locations')) {
            return;
        }

        Schema::create('menu_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('location', 120)->unique();
            $table->timestamps();

            $table->index(['menu_id'], 'menu_locations_menu_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_locations');
    }
};
