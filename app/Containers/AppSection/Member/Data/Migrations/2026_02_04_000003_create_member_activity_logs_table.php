<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('action', 120);
            $table->text('user_agent')->nullable();
            $table->string('reference_url')->nullable();
            $table->string('reference_name')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->foreignId('member_id')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_activity_logs');
    }
};
