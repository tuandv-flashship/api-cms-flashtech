<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('slugs', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('reference_type');
            $table->string('prefix', 120)->nullable()->default('');
            $table->timestamps();

            $table->index('key', 'slugs_key_index');
            $table->index('prefix', 'slugs_prefix_index');
            $table->index(['key', 'prefix'], 'slugs_key_prefix_index');
            $table->index(['reference_type', 'reference_id'], 'slugs_reference_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slugs');
    }
};
