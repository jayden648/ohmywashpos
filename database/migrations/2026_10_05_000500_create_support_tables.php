<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // Percentage points stored as an integer so no float arithmetic is used.
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->decimal('minimum_total', 15, 2)->default(0);
            $table->decimal('maximum_discount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('pcs');
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['quantity', 'minimum_stock']);
        });

        // A dedicated counter table gives a gap-free, race-safe daily order number.
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->date('sequence_date');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique('sequence_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('promotions');
    }
};