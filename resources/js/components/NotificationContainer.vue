<template>
    <Teleport to="body">
        <div class="fixed top-20 right-4 z-50 space-y-3 max-w-sm w-full">
            <TransitionGroup name="notification" tag="div" class="space-y-3">
                <div v-for="notification in notifications" :key="notification.id" :class="[
                    'rounded-lg border p-4 shadow-lg backdrop-blur-sm',
                    'transform transition-all duration-300 ease-in-out',
                    notificationClasses[notification.type]
                ]">
                    <div class="flex items-center gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <component :is="getIcon(notification.type)" :class="[
                                'h-6 w-6',
                                iconClasses[notification.type]
                            ]" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p :class="[
                                'text-sm font-medium leading-5',
                                titleClasses[notification.type]
                            ]">
                                {{ notification.title }}
                            </p>
                            <p v-if="notification.message" :class="[
                                'text-sm mt-1 leading-5',
                                messageClasses[notification.type]
                            ]">
                                {{ notification.message }}
                            </p>
                        </div>

                        <!-- Close Button -->
                        <button @click="removeNotification(notification.id)" :class="[
                            'flex-shrink-0 rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 hover:bg-black/5 dark:hover:bg-white/5 transition-colors',
                            closeButtonClasses[notification.type]
                        ]">
                            <span class="sr-only">Close</span>
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { CheckCircle, AlertTriangle, XCircle, Info, X } from 'lucide-vue-next';
import { useNotifications } from '@/composables/useNotifications';

const { notifications, removeNotification } = useNotifications();

const getIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'error': return XCircle;
        case 'warning': return AlertTriangle;
        case 'info': return Info;
        default: return Info;
    }
};

const notificationClasses = {
    success: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
    error: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
    warning: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
    info: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
};

const iconClasses = {
    success: 'text-green-400 dark:text-green-300',
    error: 'text-red-400 dark:text-red-300',
    warning: 'text-yellow-400 dark:text-yellow-300',
    info: 'text-blue-400 dark:text-blue-300',
};

const titleClasses = {
    success: 'text-green-800 dark:text-green-200',
    error: 'text-red-800 dark:text-red-200',
    warning: 'text-yellow-800 dark:text-yellow-200',
    info: 'text-blue-800 dark:text-blue-200',
};

const messageClasses = {
    success: 'text-green-700 dark:text-green-300',
    error: 'text-red-700 dark:text-red-300',
    warning: 'text-yellow-700 dark:text-yellow-300',
    info: 'text-blue-700 dark:text-blue-300',
};

const closeButtonClasses = {
    success: 'text-green-800 dark:text-green-200 focus:ring-green-500',
    error: 'text-red-800 dark:text-red-200 focus:ring-red-500',
    warning: 'text-yellow-800 dark:text-yellow-200 focus:ring-yellow-500',
    info: 'text-blue-800 dark:text-blue-200 focus:ring-blue-500',
};
</script>

<style scoped>
.notification-enter-active {
    transition: all 0.3s ease-out;
}

.notification-leave-active {
    transition: all 0.3s ease-in;
}

.notification-enter-from {
    opacity: 0;
    transform: translateX(100%) scale(0.95);
}

.notification-leave-to {
    opacity: 0;
    transform: translateX(100%) scale(0.95);
}

.notification-enter-to,
.notification-leave-from {
    opacity: 1;
    transform: translateX(0) scale(1);
}

.notification-move {
    transition: transform 0.3s ease;
}
</style>
