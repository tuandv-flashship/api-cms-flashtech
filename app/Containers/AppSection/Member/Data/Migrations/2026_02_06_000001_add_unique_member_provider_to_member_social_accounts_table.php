<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('member_social_accounts', static function (Blueprint $table): void {
            $table->unique(
                ['member_id', 'provider'],
                'member_social_accounts_member_id_provider_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('member_social_accounts', static function (Blueprint $table): void {
            $table->dropUnique('member_social_accounts_member_id_provider_unique');
        });
    }
};
