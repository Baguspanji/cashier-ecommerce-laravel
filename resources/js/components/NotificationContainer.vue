<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <TransitionGroup name="notification" tag="div">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'max-w-sm rounded-lg shadow-lg border p-4 relative',
            'transform transition-all duration-300 ease-in-out',
            notificationClasses[notification.type]
          ]"
        >
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <component :is="getIcon(notification.type)" :class="iconClasses[notification.type]" />
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p :class="titleClasses[notification.type]">
                {{ notification.title }}
              </p>
              <p :class="messageClasses[notification.type]">
                {{ notification.message }}
              </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                :class="[
                  'rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2',
                  closeButtonClasses[notification.type]
                ]"
              >
                <span class="sr-only">Close</span>
                <XIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { CheckCircleIcon, AlertTriangleIcon, XCircleIcon, InfoIcon, XIcon } from 'lucide-vue-next';
import { useNotifications } from '@/composables/useNotifications';

const { notifications, removeNotification } = useNotifications();

const getIcon = (type: string) => {
  switch (type) {
    case 'success': return CheckCircleIcon;
    case 'error': return XCircleIcon;
    case 'warning': return AlertTriangleIcon;
    case 'info': return InfoIcon;
    default: return InfoIcon;
  }
};

const notificationClasses = {
  success: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
  error: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
  warning: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
  info: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
};

const iconClasses = {
  success: 'h-5 w-5 text-green-500',
  error: 'h-5 w-5 text-red-500',
  warning: 'h-5 w-5 text-yellow-500',
  info: 'h-5 w-5 text-blue-500',
};

const titleClasses = {
  success: 'text-sm font-medium text-green-800 dark:text-green-200',
  error: 'text-sm font-medium text-red-800 dark:text-red-200',
  warning: 'text-sm font-medium text-yellow-800 dark:text-yellow-200',
  info: 'text-sm font-medium text-blue-800 dark:text-blue-200',
};

const messageClasses = {
  success: 'mt-1 text-sm text-green-700 dark:text-green-300',
  error: 'mt-1 text-sm text-red-700 dark:text-red-300',
  warning: 'mt-1 text-sm text-yellow-700 dark:text-yellow-300',
  info: 'mt-1 text-sm text-blue-700 dark:text-blue-300',
};

const closeButtonClasses = {
  success: 'focus:ring-green-500',
  error: 'focus:ring-red-500',
  warning: 'focus:ring-yellow-500',
  info: 'focus:ring-blue-500',
};
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
