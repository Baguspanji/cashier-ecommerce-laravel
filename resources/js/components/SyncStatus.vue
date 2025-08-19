<template>
  <div class="fixed bottom-4 right-4 z-50">
    <!-- Sync Status Card -->
    <div
      v-if="showSyncStatus"
      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 mb-4 max-w-sm"
    >
      <!-- Online/Offline Status -->
      <div class="flex items-center mb-2">
        <div :class="[
          'w-3 h-3 rounded-full mr-2',
          isOnline ? 'bg-green-500' : 'bg-red-500'
        ]"></div>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
          {{ isOnline ? 'Online' : 'Offline' }}
        </span>
      </div>

      <!-- Pending Transactions -->
      <div v-if="pendingCount > 0" class="mb-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-600 dark:text-gray-400">
            Pending sync:
          </span>
          <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
            {{ pendingCount }} transactions
          </span>
        </div>
      </div>

      <!-- Sync Error -->
      <div v-if="syncError" class="mb-3">
        <div class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded">
          {{ syncError }}
        </div>
      </div>

      <!-- Last Sync Result -->
      <div v-if="lastSyncResult && !syncError" class="mb-3">
        <div class="text-xs text-green-600 dark:text-green-400">
          Last sync: {{ lastSyncResult.syncedCount }} successful
          <span v-if="lastSyncResult.failedCount > 0">
            , {{ lastSyncResult.failedCount }} failed
          </span>
        </div>
      </div>

      <!-- Sync Button -->
      <button
        @click="handleSync"
        :disabled="!isOnline || isSyncing || pendingCount === 0"
        class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
        :class="[
          isOnline && pendingCount > 0
            ? 'bg-blue-600 hover:bg-blue-700 text-white'
            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
        ]"
      >
        <svg
          v-if="isSyncing"
          class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <svg
          v-else
          class="-ml-1 mr-2 h-4 w-4"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
        {{ getSyncButtonText() }}
      </button>

      <!-- Manual Controls -->
      <div class="mt-3 flex gap-2">
        <button
          @click="showPendingTransactions = !showPendingTransactions"
          class="flex-1 px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
        >
          {{ showPendingTransactions ? 'Hide' : 'Show' }} Pending
        </button>
        <button
          @click="clearData"
          class="flex-1 px-2 py-1 text-xs bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded hover:bg-red-200 dark:hover:bg-red-900/40"
        >
          Clear All
        </button>
      </div>

      <!-- Pending Transactions List -->
      <div v-if="showPendingTransactions && pendingTransactions.length > 0" class="mt-3 border-t pt-3">
        <div class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">
          Pending Transactions:
        </div>
        <div class="max-h-32 overflow-y-auto space-y-2">
          <div
            v-for="transaction in pendingTransactions"
            :key="transaction.offline_id"
            class="text-xs p-2 bg-gray-50 dark:bg-gray-700 rounded"
          >
            <div class="font-medium">{{ formatCurrency(transaction.total_amount) }}</div>
            <div class="text-gray-500 dark:text-gray-400">
              {{ transaction.customer_name || 'No customer' }} •
              {{ transaction.items.length }} items
            </div>
            <div class="text-gray-400 dark:text-gray-500">
              {{ formatDate(transaction.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Sync Button (when status card is hidden) -->
    <button
      v-if="!showSyncStatus && (pendingCount > 0 || !isOnline)"
      @click="showSyncStatus = true"
      class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-3 shadow-lg relative"
    >
      <svg
        class="h-6 w-6"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
        />
      </svg>

      <!-- Badge for pending count -->
      <span
        v-if="pendingCount > 0"
        class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center"
      >
        {{ pendingCount > 99 ? '99+' : pendingCount }}
      </span>

      <!-- Offline indicator -->
      <span
        v-if="!isOnline"
        class="absolute -bottom-1 -right-1 bg-red-500 rounded-full h-3 w-3"
      ></span>
    </button>

    <!-- Toggle visibility button -->
    <button
      v-if="showSyncStatus"
      @click="showSyncStatus = false"
      class="absolute top-1 right-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
    >
      <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watchEffect } from 'vue';
import { useOfflineSync } from '@/composables/useOfflineSync';
import type { OfflineTransaction } from '@/offlineSync';

const {
  pendingCount,
  isOnline,
  isSyncing,
  lastSyncResult,
  syncError,
  syncPendingTransactions,
  getPendingTransactions,
  clearOfflineData,
} = useOfflineSync();

const showSyncStatus = ref(false);
const showPendingTransactions = ref(false);
const pendingTransactions = ref<OfflineTransaction[]>([]);

// Auto-show sync status when there are pending transactions or offline
const shouldAutoShow = computed(() => {
  return pendingCount.value > 0 || !isOnline.value;
});

// Watch for auto-show conditions
watchEffect(() => {
  if (shouldAutoShow.value && !showSyncStatus.value) {
    showSyncStatus.value = true;
  }
});

const handleSync = async () => {
  await syncPendingTransactions();
  await loadPendingTransactions();
};

const clearData = async () => {
  if (confirm('Are you sure you want to clear all offline data? This cannot be undone.')) {
    await clearOfflineData();
    await loadPendingTransactions();
  }
};

const loadPendingTransactions = async () => {
  pendingTransactions.value = await getPendingTransactions();
};

const getSyncButtonText = () => {
  if (isSyncing.value) return 'Syncing...';
  if (!isOnline.value) return 'Offline';
  if (pendingCount.value === 0) return 'No pending data';
  return `Sync ${pendingCount.value} transactions`;
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
  }).format(amount);
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('id-ID', {
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
