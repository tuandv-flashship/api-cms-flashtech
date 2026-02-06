<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('push_token_hash', 64)->nullable()->after('push_token');
            $table->unique(['push_provider', 'push_token_hash'], 'devices_push_provider_token_hash_unique');
        });

        DB::table('devices')
            ->select('id', 'push_token')
            ->whereNotNull('push_token')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $hash = hash('sha256', (string) $row->push_token);
                    DB::table('devices')
                        ->where('id', $row->id)
                        ->update(['push_token_hash' => $hash]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropUnique('devices_push_provider_token_hash_unique');
            $table->dropColumn('push_token_hash');
        });
    }
};
