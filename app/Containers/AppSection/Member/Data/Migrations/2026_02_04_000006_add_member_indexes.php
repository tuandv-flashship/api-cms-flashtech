<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', static function (Blueprint $table): void {
            $table->index('status');
            $table->index('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', static function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['email_verified_at']);
        });
    }
};
