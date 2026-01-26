<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('slugs')) {
            return;
        }

        if (! Schema::hasIndex('slugs', 'slugs_key_prefix_unique')) {
            Schema::table('slugs', function (Blueprint $table): void {
                $table->unique(['key', 'prefix'], 'slugs_key_prefix_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('slugs') && Schema::hasIndex('slugs', 'slugs_key_prefix_unique')) {
            Schema::table('slugs', function (Blueprint $table): void {
                $table->dropIndex('slugs_key_prefix_unique');
            });
        }
    }
};
