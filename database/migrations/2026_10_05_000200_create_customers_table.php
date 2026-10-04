<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            // Stored in national format (08...) and normalised on search.
            $table->string('phone', 32);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            // Customers are deactivated rather than deleted so order history survives.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['phone']);
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};