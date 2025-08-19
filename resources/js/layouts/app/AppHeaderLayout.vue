<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import PWAUpdatePrompt from '@/components/PWAUpdatePrompt.vue';
import AppAlert from '@/components/AppAlert.vue';
import SyncStatus from '@/components/SyncStatus.vue';
import NotificationContainer from '@/components/NotificationContainer.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
        <PWAUpdatePrompt />
        <AppAlert />
        <SyncStatus />
        <NotificationContainer />
    </AppShell>
</template>
