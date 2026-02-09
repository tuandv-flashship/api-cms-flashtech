<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table): void {
            if (! Schema::hasIndex('languages', 'languages_lang_locale_index')) {
                $table->index('lang_locale', 'languages_lang_locale_index');
            }

            if (! Schema::hasIndex('languages', 'languages_lang_is_default_index')) {
                $table->index('lang_is_default', 'languages_lang_is_default_index');
            }

            if (! Schema::hasIndex('languages', 'languages_lang_order_index')) {
                $table->index('lang_order', 'languages_lang_order_index');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table): void {
            if (Schema::hasIndex('languages', 'languages_lang_locale_index')) {
                $table->dropIndex('languages_lang_locale_index');
            }

            if (Schema::hasIndex('languages', 'languages_lang_is_default_index')) {
                $table->dropIndex('languages_lang_is_default_index');
            }

            if (Schema::hasIndex('languages', 'languages_lang_order_index')) {
                $table->dropIndex('languages_lang_order_index');
            }
        });
    }
};
