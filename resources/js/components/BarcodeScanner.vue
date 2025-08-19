<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { CameraIcon, XIcon, KeyboardIcon, ScanIcon } from 'lucide-vue-next'

interface Props {
    isOpen: boolean
    title?: string
}

interface Emits {
    close: []
    scanned: [barcode: string]
}

const emit = defineEmits<Emits>()

defineProps<Props>()

// State
const isScanning = ref(false)
const isCameraSupported = ref(false)
const error = ref('')
const manualBarcode = ref('')
const showManualInput = ref(false)

// Camera refs
const videoElement = ref<HTMLVideoElement>()
const canvasElement = ref<HTMLCanvasElement>()
const stream = ref<MediaStream>()

// Check camera support
onMounted(() => {
    isCameraSupported.value = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)
})

// Cleanup on unmount
onUnmounted(() => {
    stopCamera()
})

const startCamera = async () => {
    try {
        error.value = ''
        isScanning.value = true

        // Request camera access
        stream.value = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'environment', // Use back camera on mobile
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        })

        if (videoElement.value) {
            videoElement.value.srcObject = stream.value
            await videoElement.value.play()

            // Start scanning simulation (in real implementation, this would use a barcode library)
            simulateScanning()
        }
    } catch (err) {
        console.error('Camera access error:', err)
        error.value = 'Tidak dapat mengakses kamera. Pastikan permission diberikan.'
        isScanning.value = false
    }
}

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop())
        stream.value = undefined
    }
    isScanning.value = false
}

// Simulate barcode scanning (replace with real scanning library)
const simulateScanning = () => {
    // In real implementation, this would use a barcode scanning library
    // For now, we'll simulate with a timeout
    setTimeout(() => {
        if (isScanning.value) {
            // Simulate finding a barcode
            const mockBarcode = '1234567890123'
            handleBarcodeDetected(mockBarcode)
        }
    }, 3000)
}

const handleBarcodeDetected = (barcode: string) => {
    stopCamera()
    emit('scanned', barcode)
    emit('close')
}

const handleManualInput = () => {
    if (manualBarcode.value.trim()) {
        emit('scanned', manualBarcode.value.trim())
        emit('close')
        manualBarcode.value = ''
    }
}

const handleClose = () => {
    stopCamera()
    showManualInput.value = false
    manualBarcode.value = ''
    error.value = ''
    emit('close')
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <ScanIcon class="h-5 w-5" />
                    {{ title || 'Scan Barcode' }}
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
                <!-- Camera not supported -->
                <div v-if="!isCameraSupported" class="text-center p-4">
                    <p class="text-muted-foreground mb-4">
                        Kamera tidak didukung di perangkat ini
                    </p>
                    <Button @click="showManualInput = true" variant="outline" class="w-full">
                        <KeyboardIcon class="mr-2 h-4 w-4" />
                        Input Manual
                    </Button>
                </div>

                <!-- Camera supported but not scanning -->
                <div v-else-if="!isScanning && !showManualInput" class="space-y-4">
                    <div class="text-center p-4">
                        <CameraIcon class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p class="text-muted-foreground mb-4">
                            Pilih metode untuk memasukkan barcode
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Button @click="startCamera" class="w-full">
                            <CameraIcon class="mr-2 h-4 w-4" />
                            Scan dengan Kamera
                        </Button>
                        <Button @click="showManualInput = true" variant="outline" class="w-full">
                            <KeyboardIcon class="mr-2 h-4 w-4" />
                            Input Manual
                        </Button>
                    </div>
                </div>

                <!-- Manual input -->
                <div v-if="showManualInput" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="manual-barcode">Masukkan Kode Barcode</Label>
                        <Input
                            id="manual-barcode"
                            v-model="manualBarcode"
                            placeholder="1234567890123"
                            @keyup.enter="handleManualInput"
                        />
                    </div>
                    <div class="flex gap-2">
                        <Button @click="handleManualInput" :disabled="!manualBarcode.trim()" class="flex-1">
                            Tambahkan
                        </Button>
                        <Button @click="showManualInput = false" variant="outline">
                            Kembali
                        </Button>
                    </div>
                </div>

                <!-- Camera view -->
                <div v-if="isScanning" class="space-y-4">
                    <Card>
                        <CardContent class="p-0">
                            <div class="relative bg-black rounded-lg overflow-hidden aspect-video">
                                <video
                                    ref="videoElement"
                                    class="w-full h-full object-cover"
                                    autoplay
                                    muted
                                    playsinline
                                />
                                <canvas
                                    ref="canvasElement"
                                    class="absolute inset-0 w-full h-full"
                                    style="display: none"
                                />

                                <!-- Scanning overlay -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="border-2 border-primary bg-primary/10 rounded-lg w-64 h-32 flex items-center justify-center">
                                        <div class="text-primary-foreground text-sm font-medium bg-primary/20 px-2 py-1 rounded">
                                            Arahkan ke barcode
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div class="text-center">
                        <p class="text-sm text-muted-foreground mb-4">
                            Posisikan barcode di dalam kotak untuk memindai
                        </p>
                        <Button @click="stopCamera" variant="outline">
                            <XIcon class="mr-2 h-4 w-4" />
                            Batal
                        </Button>
                    </div>
                </div>

                <!-- Error message -->
                <div v-if="error" class="text-center p-4 bg-destructive/10 rounded-lg">
                    <p class="text-destructive text-sm">{{ error }}</p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
