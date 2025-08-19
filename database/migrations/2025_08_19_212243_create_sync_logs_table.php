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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('operation'); // create, update, delete, etc
            $table->string('table_name', 50)->index();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('offline_id', 50)->nullable()->index();
            $table->json('data')->nullable(); // Data yang akan disinkronkan
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['table_name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
