<template>
  <div class="p-6 space-y-6">
    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Test Offline Sync</h2>

    <!-- Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h3 class="font-medium text-gray-900 dark:text-gray-100">Connection Status</h3>
        <div class="flex items-center mt-2">
          <div :class="[
            'w-3 h-3 rounded-full mr-2',
            isOnline ? 'bg-green-500' : 'bg-red-500'
          ]"></div>
          <span :class="[
            'font-medium',
            isOnline ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
          ]">
            {{ isOnline ? 'Online' : 'Offline' }}
          </span>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h3 class="font-medium text-gray-900 dark:text-gray-100">Pending Transactions</h3>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">
          {{ pendingCount }}
        </p>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-lg border p-4">
        <h3 class="font-medium text-gray-900 dark:text-gray-100">Sync Status</h3>
        <p class="text-sm mt-2" :class="[
          isSyncing ? 'text-blue-600' : 'text-gray-600 dark:text-gray-400'
        ]">
          {{ isSyncing ? 'Syncing...' : 'Idle' }}
        </p>
      </div>
    </div>

    <!-- Actions -->
    <div class="space-y-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg border p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Test Actions</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button
            @click="createOfflineTransaction"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Create Test Offline Transaction
          </button>

          <button
            @click="forceSync"
            :disabled="!isOnline || isSyncing || pendingCount === 0"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Force Sync
          </button>

          <button
            @click="simulateOffline"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
          >
            {{ simulatingOffline ? 'Go Online' : 'Simulate Offline' }}
          </button>

          <button
            @click="clearAllData"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Clear All Offline Data
          </button>
        </div>
      </div>

      <!-- Pending Transactions -->
      <div v-if="pendingTransactions.length > 0" class="bg-white dark:bg-gray-800 rounded-lg border p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Pending Transactions</h3>

        <div class="space-y-3">
          <div
            v-for="transaction in pendingTransactions"
            :key="transaction.offline_id"
            class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                  {{ formatCurrency(transaction.total_amount) }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ transaction.payment_method }} • {{ transaction.items.length }} items
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                  {{ formatDate(transaction.created_at) }}
                </p>
              </div>
              <span class="text-xs px-2 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded">
                Pending
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sync Errors -->
      <div v-if="syncError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Sync Error</h3>
        <p class="text-sm text-red-700 dark:text-red-300">{{ syncError }}</p>
      </div>

      <!-- Last Sync Result -->
      <div v-if="lastSyncResult" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">Last Sync Result</h3>
        <div class="text-sm text-green-700 dark:text-green-300 space-y-1">
          <p>Synced: {{ lastSyncResult.syncedCount }}</p>
          <p>Failed: {{ lastSyncResult.failedCount }}</p>
          <p v-if="lastSyncResult.errors.length > 0">
            Errors: {{ lastSyncResult.errors.join(', ') }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useOfflineSync } from '@/composables/useOfflineSync';
import { useNotifications } from '@/composables/useNotifications';
import type { OfflineTransaction } from '@/offlineSync';

const {
  pendingCount,
  isOnline,
  isSyncing,
  lastSyncResult,
  syncError,
  storeOfflineTransaction,
  syncPendingTransactions,
  getPendingTransactions,
  clearOfflineData,
} = useOfflineSync();

const { addNotification } = useNotifications();

const pendingTransactions = ref<OfflineTransaction[]>([]);
const simulatingOffline = ref(false);

const loadPendingTransactions = async () => {
  pendingTransactions.value = await getPendingTransactions();
};

const createOfflineTransaction = async () => {
  try {
    const testTransaction = {
      customer_name: 'Test Customer',
      total_amount: Math.floor(Math.random() * 100000) + 10000, // Random amount 10k-110k
      payment_method: 'cash' as const,
      items: [
        {
          product_id: 1, // Assuming product ID 1 exists
          quantity: Math.floor(Math.random() * 5) + 1, // Random 1-5
          price: Math.floor(Math.random() * 50000) + 5000, // Random 5k-55k
        }
      ],
      created_at: new Date().toISOString(),
    };

    await storeOfflineTransaction(testTransaction);
    await loadPendingTransactions();

    addNotification({
      type: 'success',
      title: 'Test Transaction Created',
      message: `Offline transaction for ${formatCurrency(testTransaction.total_amount)} created`
    });
  } catch (error) {
    console.error('Failed to create test transaction:', error);
    addNotification({
      type: 'error',
      title: 'Failed to Create Transaction',
      message: 'Could not create test transaction'
    });
  }
};

const forceSync = async () => {
  try {
    const result = await syncPendingTransactions();
    await loadPendingTransactions();

    if (result?.success) {
      addNotification({
        type: 'success',
        title: 'Sync Completed',
        message: `Synced ${result.syncedCount} transactions successfully`
      });
    } else {
      addNotification({
        type: 'error',
        title: 'Sync Failed',
        message: result?.errors.join(', ') || 'Unknown error'
      });
    }
  } catch (error) {
    console.error('Sync failed:', error);
    addNotification({
      type: 'error',
      title: 'Sync Error',
      message: 'Failed to sync transactions'
    });
  }
};

const simulateOffline = () => {
  simulatingOffline.value = !simulatingOffline.value;
  // Note: This doesn't actually change the network status,
  // just for testing purposes. You would need to disable network in DevTools.
  addNotification({
    type: 'info',
    title: simulatingOffline.value ? 'Simulating Offline' : 'Simulating Online',
    message: 'Use DevTools to actually control network status'
  });
};

const clearAllData = async () => {
  if (confirm('Are you sure you want to clear all offline data? This cannot be undone.')) {
    try {
      await clearOfflineData();
      await loadPendingTransactions();

      addNotification({
        type: 'success',
        title: 'Data Cleared',
        message: 'All offline data has been cleared'
      });
    } catch (error) {
      console.error('Failed to clear data:', error);
      addNotification({
        type: 'error',
        title: 'Clear Failed',
        message: 'Could not clear offline data'
      });
    }
  }
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

onMounted(() => {
  loadPendingTransactions();
});
</script>
