<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { X, CheckCircle, AlertTriangle, Info, XCircle } from 'lucide-vue-next';

interface Flash {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
    message?: string;
}

const page = usePage();
const flash = computed<Flash>(() => page.props.flash as Flash);
const isVisible = ref(false);
const currentAlert = ref<{ type: string; message: string; icon: any; bgColor: string; textColor: string; borderColor: string } | null>(null);

const alertTypes = {
    success: {
        icon: CheckCircle,
        bgColor: 'bg-green-50 dark:bg-green-900/20',
        textColor: 'text-green-800 dark:text-green-200',
        borderColor: 'border-green-200 dark:border-green-800',
        iconColor: 'text-green-400 dark:text-green-300'
    },
    error: {
        icon: XCircle,
        bgColor: 'bg-red-50 dark:bg-red-900/20',
        textColor: 'text-red-800 dark:text-red-200',
        borderColor: 'border-red-200 dark:border-red-800',
        iconColor: 'text-red-400 dark:text-red-300'
    },
    warning: {
        icon: AlertTriangle,
        bgColor: 'bg-yellow-50 dark:bg-yellow-900/20',
        textColor: 'text-yellow-800 dark:text-yellow-200',
        borderColor: 'border-yellow-200 dark:border-yellow-800',
        iconColor: 'text-yellow-400 dark:text-yellow-300'
    },
    info: {
        icon: Info,
        bgColor: 'bg-blue-50 dark:bg-blue-900/20',
        textColor: 'text-blue-800 dark:text-blue-200',
        borderColor: 'border-blue-200 dark:border-blue-800',
        iconColor: 'text-blue-400 dark:text-blue-300'
    },
    message: {
        icon: Info,
        bgColor: 'bg-gray-50 dark:bg-gray-900/20',
        textColor: 'text-gray-800 dark:text-gray-200',
        borderColor: 'border-gray-200 dark:border-gray-800',
        iconColor: 'text-gray-400 dark:text-gray-300'
    }
};

const showAlert = () => {
    // Cek flash messages dan tampilkan yang pertama ditemukan
    for (const [type, message] of Object.entries(flash.value)) {
        if (message && message.trim()) {
            const alertConfig = alertTypes[type as keyof typeof alertTypes];
            if (alertConfig) {
                currentAlert.value = {
                    type,
                    message: message.trim(),
                    icon: alertConfig.icon,
                    bgColor: alertConfig.bgColor,
                    textColor: alertConfig.textColor,
                    borderColor: alertConfig.borderColor
                };
                isVisible.value = true;

                // Auto hide after 5 seconds
                setTimeout(() => {
                    hideAlert();
                }, 5000);

                break; // Hanya tampilkan satu alert
            }
        }
    }
};

const hideAlert = () => {
    isVisible.value = false;
    setTimeout(() => {
        currentAlert.value = null;
    }, 300); // Wait for animation to complete
};

// Watch for changes in flash messages
watch(flash, () => {
    nextTick(() => {
        showAlert();
    });
}, { immediate: true, deep: true });

// Listen for custom flash events from frontend
const handleCustomFlashEvent = (event: Event) => {
    const customEvent = event as CustomEvent;
    const { type, message } = customEvent.detail;
    const alertConfig = alertTypes[type as keyof typeof alertTypes];

    if (alertConfig && message) {
        currentAlert.value = {
            type,
            message: message.trim(),
            icon: alertConfig.icon,
            bgColor: alertConfig.bgColor,
            textColor: alertConfig.textColor,
            borderColor: alertConfig.borderColor
        };
        isVisible.value = true;

        // Auto hide after 5 seconds
        setTimeout(() => {
            hideAlert();
        }, 5000);
    }
};

onMounted(() => {
    showAlert();
    // Listen for custom flash events
    window.addEventListener('flash-message', handleCustomFlashEvent);
});

// Cleanup event listener
import { onUnmounted } from 'vue';
onUnmounted(() => {
    window.removeEventListener('flash-message', handleCustomFlashEvent);
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 transform translate-y-2 scale-95"
            enter-to-class="opacity-100 transform translate-y-0 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 transform translate-y-0 scale-100"
            leave-to-class="opacity-0 transform translate-y-2 scale-95">
            <div v-if="isVisible && currentAlert" class="fixed top-4 right-4 z-50 max-w-sm w-full">
                <div :class="[
                    'rounded-lg border p-4 shadow-lg backdrop-blur-sm',
                    currentAlert.bgColor,
                    currentAlert.borderColor
                ]">
                    <div class="flex items-center gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <component :is="currentAlert.icon" :class="[
                                'h-6 w-6',
                                alertTypes[currentAlert.type as keyof typeof alertTypes].iconColor
                            ]" />
                        </div>

                        <!-- Message -->
                        <div class="flex-1">
                            <p :class="[
                                'text-sm font-medium',
                                currentAlert.textColor
                            ]">
                                {{ currentAlert.message }}
                            </p>
                        </div>

                        <!-- Close Button -->
                        <button @click="hideAlert" :class="[
                            'flex-shrink-0 rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 hover:bg-black/5 dark:hover:bg-white/5',
                            currentAlert.textColor
                        ]">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
