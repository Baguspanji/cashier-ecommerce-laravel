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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('sync_status', 20)->default('synced')->index()->after('subtotal');
            $table->string('offline_id', 50)->nullable()->after('sync_status');

            // Add index for sync operations
            $table->index(['sync_status', 'offline_id'], 'transaction_items_sync_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex('transaction_items_sync_index');
            $table->dropColumn(['sync_status', 'offline_id']);
        });
    }
};
