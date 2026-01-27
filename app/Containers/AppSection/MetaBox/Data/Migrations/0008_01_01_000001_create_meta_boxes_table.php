<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('meta_boxes', function (Blueprint $table): void {
            $table->id();
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->foreignId('reference_id')->index();
            $table->string('reference_type', 120);
            $table->timestamps();

            $table->index(['reference_type', 'reference_id', 'meta_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_boxes');
    }
};
