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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // 'in', 'out', 'adjustment'
            $table->integer('quantity');
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->unsignedBigInteger('reference_id')->nullable(); // transaction_id for 'out' movements
            $table->string('reference_type', 50)->nullable(); // 'transaction', 'manual', 'initial'
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
