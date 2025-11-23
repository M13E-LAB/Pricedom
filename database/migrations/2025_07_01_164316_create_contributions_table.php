<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('product_barcode')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('store_name')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_image')->nullable();
            $table->enum('contribution_type', ['scan', 'manual'])->default('manual');
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
