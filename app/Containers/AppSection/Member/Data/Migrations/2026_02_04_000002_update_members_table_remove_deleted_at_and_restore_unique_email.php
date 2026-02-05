<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('members', 'deleted_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }

        try {
            Schema::table('members', function (Blueprint $table) {
                $table->dropIndex('members_email_index');
            });
        } catch (\Throwable) {
            // Index may not exist.
        }

        try {
            Schema::table('members', function (Blueprint $table) {
                $table->unique('email');
            });
        } catch (\Throwable) {
            // Unique may already exist.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique('members_email_unique');
            });
        } catch (\Throwable) {
            // Unique may not exist.
        }

        Schema::table('members', function (Blueprint $table) {
            $table->index('email');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
