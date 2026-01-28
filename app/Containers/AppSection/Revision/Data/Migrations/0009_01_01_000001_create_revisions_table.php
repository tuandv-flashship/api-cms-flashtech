<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('revisions')) {
            Schema::create('revisions', function (Blueprint $table): void {
                $table->id();
                $table->string('revisionable_type');
                $table->unsignedBigInteger('revisionable_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('key', 120);
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->timestamps();

                $table->index(['revisionable_id', 'revisionable_type'], 'revisions_revisionable_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
