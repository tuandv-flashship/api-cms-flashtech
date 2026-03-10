<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('field_groups_translations', function (Blueprint $table): void {
            $table->foreign('field_groups_id', 'fk_fg_trans_field_groups')
                ->references('id')
                ->on('field_groups')
                ->onDelete('cascade');
        });

        Schema::table('field_items_translations', function (Blueprint $table): void {
            $table->foreign('field_items_id', 'fk_fi_trans_field_items')
                ->references('id')
                ->on('field_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('field_groups_translations', function (Blueprint $table): void {
            $table->dropForeign('fk_fg_trans_field_groups');
        });

        Schema::table('field_items_translations', function (Blueprint $table): void {
            $table->dropForeign('fk_fi_trans_field_items');
        });
    }
};
