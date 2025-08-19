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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('sync_status', 20)->default('synced')->index()->after('status');
            $table->string('offline_id', 50)->nullable()->unique()->after('sync_status');
            $table->timestamp('last_sync_at')->nullable()->after('offline_id');
            $table->json('sync_metadata')->nullable()->after('last_sync_at');

            // Add composite index for sync operations
            $table->index(['sync_status', 'offline_id'], 'transactions_sync_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_sync_composite');
            $table->dropColumn(['sync_status', 'offline_id', 'last_sync_at', 'sync_metadata']);
        });
    }
};
