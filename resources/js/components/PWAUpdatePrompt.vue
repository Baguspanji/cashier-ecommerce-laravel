<template>
    <div
        v-if="showUpdatePrompt"
        class="fixed bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 max-w-sm z-50"
    >
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Update Available
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    A new version of the app is available. Update now for the latest features and improvements.
                </p>
                <div class="mt-3 flex space-x-2">
                    <button
                        @click="updateApp"
                        :disabled="updating"
                        class="bg-blue-600 text-white px-3 py-1 text-xs font-medium rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ updating ? 'Updating...' : 'Update' }}
                    </button>
                    <button
                        @click="dismissUpdate"
                        class="bg-gray-200 text-gray-800 px-3 py-1 text-xs font-medium rounded hover:bg-gray-300"
                    >
                        Later
                    </button>
                </div>
            </div>
            <button
                @click="dismissUpdate"
                class="flex-shrink-0 text-gray-400 hover:text-gray-600"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const showUpdatePrompt = ref(false)
const updating = ref(false)

let updateSW: ((reloadPage?: boolean) => Promise<void>) | undefined

onMounted(async () => {
    // Check if PWA update is available
    if ('serviceWorker' in navigator) {
        const { registerSW } = await import('virtual:pwa-register')

        updateSW = registerSW({
            onNeedRefresh() {
                showUpdatePrompt.value = true
            },
            onOfflineReady() {
                console.log('App is ready to work offline')
            },
            onRegistered(registration) {
                console.log('SW registered: ', registration)
            },
            onRegisterError(error) {
                console.log('SW registration error', error)
            }
        })
    }
})

const updateApp = async () => {
    if (updateSW) {
        updating.value = true
        try {
            await updateSW(true)
        } catch (error) {
            console.error('Failed to update app:', error)
            updating.value = false
        }
    }
}

const dismissUpdate = () => {
    showUpdatePrompt.value = false
}
</script>
