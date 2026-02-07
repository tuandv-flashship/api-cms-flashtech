<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 50);
            $table->unsignedBigInteger('owner_id');
            $table->string('device_id', 191);
            $table->string('platform', 50)->nullable();
            $table->string('device_name', 191)->nullable();
            $table->text('push_token')->nullable();
            $table->string('push_provider', 30)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->index(['owner_type', 'owner_id', 'last_seen_at', 'id'], 'devices_owner_last_seen_id_index');
            $table->unique(['owner_type', 'owner_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
