<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('field_groups')) {
            Schema::create('field_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->text('rules')->nullable();
                $table->integer('order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('field_items')) {
            Schema::create('field_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('field_group_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->integer('order')->default(0)->nullable();
                $table->string('title');
                $table->string('slug');
                $table->string('type', 100);
                $table->text('instructions')->nullable();
                $table->text('options')->nullable();

                $table->index(['field_group_id', 'parent_id', 'order'], 'field_items_group_parent_order_index');
            });
        }

        if (! Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table): void {
                $table->id();
                $table->string('use_for');
                $table->unsignedBigInteger('use_for_id');
                $table->unsignedBigInteger('field_item_id')->index();
                $table->string('type');
                $table->string('slug');
                $table->text('value')->nullable();

                $table->index(['use_for', 'use_for_id'], 'custom_fields_use_for_index');
                $table->index(['slug', 'field_item_id'], 'custom_fields_slug_item_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('field_items');
        Schema::dropIfExists('field_groups');
    }
};
