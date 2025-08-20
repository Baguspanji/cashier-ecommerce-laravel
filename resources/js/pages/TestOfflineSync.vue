<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import OfflineSyncTest from '@/components/OfflineSyncTest.vue';

defineOptions({
    layout: AppHeaderLayout,
});
</script>

<template>
    <div class="bg-white dark:bg-gray-900 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                Test Offline Sync
            </h1>
            <OfflineSyncTest />
        </div>
    </div>
</template>
