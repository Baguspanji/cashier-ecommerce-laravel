<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { useStock, type StockMovementData, type BulkStockAdjustmentData } from '@/composables/useStock'
import { type Product } from '@/composables/useProducts'
import {
    ArrowLeftIcon,
    SaveIcon,
    PackageIcon,
    PlusIcon,
    SettingsIcon,
    TrashIcon,
    RefreshCwIcon
} from 'lucide-vue-next'

interface Props {
    products: Product[]
}

const props = defineProps<Props>()

const { loading, errors, store, bulkAdjustment, visitIndex } = useStock()

// Form modes
const mode = ref<'single' | 'bulk'>('single')

// Single adjustment form
const singleForm = reactive<StockMovementData>({
    product_id: 0,
    type: 'adjustment',
    quantity: 0,
    notes: ''
})

// Bulk adjustment form
const bulkForm = reactive<BulkStockAdjustmentData>({
    adjustments: [],
    notes: ''
})

// Bulk adjustment helper
const addBulkAdjustment = () => {
    bulkForm.adjustments.push({
        product_id: 0,
        new_stock: 0
    })
}

const removeBulkAdjustment = (index: number) => {
    bulkForm.adjustments.splice(index, 1)
}

const getProductById = (id: number) => {
    return props.products.find(p => p.id === id)
}

const handleSingleSubmit = () => {
    store(singleForm)
}

const handleBulkSubmit = () => {
    bulkAdjustment(bulkForm)
}

const handleCancel = () => {
    visitIndex()
}

// Initialize with one bulk adjustment row
addBulkAdjustment()
</script>

<template>

    <Head title="Penyesuaian Stok" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Penyesuaian Stok" description="Sesuaikan level inventori produk">
                <template #actions>
                    <Button variant="ghost" @click="visitIndex()">
                        <ArrowLeftIcon class="mr-2 h-4 w-4" />
                        Kembali ke Stok
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Mode Selector -->
            <Card>
                <CardContent>
                    <div class="flex items-center gap-4">
                        <Label>Jenis Penyesuaian:</Label>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm"
                                :class="{ 'bg-primary text-primary-foreground': mode === 'single' }"
                                @click="mode = 'single'" class="gap-2">
                                <PackageIcon class="h-4 w-4" />
                                Produk Tunggal
                            </Button>
                            <Button variant="outline" size="sm"
                                :class="{ 'bg-primary text-primary-foreground': mode === 'bulk' }"
                                @click="mode = 'bulk'" class="gap-2">
                                <SettingsIcon class="h-4 w-4" />
                                Penyesuaian Massal
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Single Product Adjustment -->
            <div v-if="mode === 'single'" class="max-w-2xl">
                <form @submit.prevent="handleSingleSubmit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Penyesuaian Produk Tunggal
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Product Selection -->
                            <div class="grid gap-2">
                                <Label for="product_id">Produk *</Label>
                                <Select v-model="singleForm.product_id">
                                    <SelectTrigger :class="{
                                        'border-destructive focus:border-destructive focus:ring-destructive': errors.product_id
                                    }">
                                        <SelectValue placeholder="Pilih produk yang akan disesuaikan" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="product in products" :key="product.id"
                                            :value="product.id.toString()">
                                            <div class="flex justify-between items-center w-full">
                                                <div>
                                                    <div class="font-medium">{{ product.name }}</div>
                                                    <div class="text-sm text-muted-foreground">{{ product.category.name
                                                        }}</div>
                                                </div>
                                                <div class="text-sm text-muted-foreground ml-4">
                                                    Stok: {{ product.current_stock }}
                                                </div>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.product_id" class="text-sm text-destructive">{{ errors.product_id }}</p>
                            </div>

                            <!-- Current Stock Display -->
                            <div v-if="singleForm.product_id" class="p-4 bg-muted rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Stok Saat Ini</p>
                                        <p class="text-lg font-semibold">
                                            {{ getProductById(parseInt(singleForm.product_id.toString()))?.current_stock
                                            || 0 }} pcs
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Stok Minimum</p>
                                        <p class="text-lg font-semibold">
                                            {{ getProductById(parseInt(singleForm.product_id.toString()))?.minimum_stock
                                            || 0 }} pcs
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Adjustment Type -->
                            <div class="grid gap-2">
                                <Label for="type">Jenis Penyesuaian *</Label>
                                <Select v-model="singleForm.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih jenis penyesuaian" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="in">Stok Masuk (+)</SelectItem>
                                        <SelectItem value="out">Stok Keluar (-)</SelectItem>
                                        <SelectItem value="adjustment">Penyesuaian Stok</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.type" class="text-sm text-destructive">{{ errors.type }}</p>
                            </div>

                            <!-- Quantity -->
                            <div class="grid gap-2">
                                <Label for="quantity">Jumlah *</Label>
                                <div class="flex items-center gap-2">
                                    <Input id="quantity" v-model.number="singleForm.quantity" type="number" min="1"
                                        placeholder="Masukkan jumlah" required :class="{
                                            'border-destructive focus:border-destructive focus:ring-destructive': errors.quantity
                                        }" class="flex-1 h-10" />
                                    <span class="text-sm text-muted-foreground">pcs</span>
                                </div>
                                <p v-if="errors.quantity" class="text-sm text-destructive">{{ errors.quantity }}</p>
                            </div>

                            <!-- New Stock Preview -->
                            <div v-if="singleForm.product_id && singleForm.quantity"
                                class="p-4 bg-primary/10 rounded-lg">
                                <p class="text-sm font-medium text-primary mb-2">Setelah Penyesuaian:</p>
                                <p class="text-xl font-bold text-primary">
                                    {{
                                        (() => {
                                            const product = getProductById(parseInt(singleForm.product_id.toString()))
                                            const currentStock = product?.current_stock || 0
                                            const quantity = singleForm.quantity || 0

                                            if (singleForm.type === 'in') return currentStock + quantity
                                            if (singleForm.type === 'out') return currentStock - quantity
                                            return quantity // For adjustment type, quantity is new stock
                                        })()
                                    }} pcs
                                </p>
                            </div>

                            <!-- Notes -->
                            <div class="grid gap-2">
                                <Label for="notes">Catatan</Label>
                                <textarea id="notes" v-model="singleForm.notes" rows="3"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Masukkan alasan penyesuaian (opsional)" />
                                <p v-if="errors.notes" class="text-sm text-destructive">{{ errors.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <Button type="submit" :disabled="loading" class="gap-2">
                            <SaveIcon class="h-4 w-4" />
                            {{ loading ? 'Memproses...' : 'Simpan Penyesuaian' }}
                        </Button>
                        <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
                            Batal
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Bulk Adjustment -->
            <div v-else class="max-w-6xl">
                <form @submit.prevent="handleBulkSubmit" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <SettingsIcon class="h-5 w-5" />
                                    Penyesuaian Stok Massal
                                </CardTitle>
                                <Button type="button" variant="outline" size="sm" @click="addBulkAdjustment"
                                    class="gap-2">
                                    <PlusIcon class="h-4 w-4" />
                                    Tambah Produk
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Adjustment Rows -->
                                <div v-for="(adjustment, index) in bulkForm.adjustments" :key="index"
                                    class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border rounded-lg">
                                    <!-- Product -->
                                    <div class="md:col-span-2">
                                        <Label :for="`product_${index}`">Produk</Label>
                                        <Select v-model="adjustment.product_id" class="h-10">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih produk" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="product in products" :key="product.id"
                                                    :value="product.id.toString()">
                                                    {{ product.name }} ({{ product.current_stock }} pcs)
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <!-- Current Stock -->
                                    <div>
                                        <Label>Stok Saat Ini</Label>
                                        <Input
                                            :value="getProductById(parseInt(adjustment.product_id.toString()))?.current_stock || 0"
                                            disabled class="bg-muted h-10" />
                                    </div>

                                    <!-- New Stock -->
                                    <div>
                                        <Label :for="`new_stock_${index}`">Stok Baru</Label>
                                        <Input :id="`new_stock_${index}`" v-model.number="adjustment.new_stock"
                                            type="number" min="0" placeholder="0" class="h-10" />
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-end">
                                        <Button type="button" variant="outline" size="sm"
                                            @click="removeBulkAdjustment(index)"
                                            :disabled="bulkForm.adjustments.length === 1" class="gap-2 w-full h-10">
                                            <TrashIcon class="h-4 w-4" />
                                            Hapus
                                        </Button>
                                    </div>
                                </div>

                                <!-- Global Notes -->
                                <div class="grid gap-2 pt-4 border-t">
                                    <Label for="bulk_notes">Catatan Global</Label>
                                    <textarea id="bulk_notes" v-model="bulkForm.notes" rows="3"
                                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Masukkan alasan penyesuaian massal (opsional)" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <Button type="submit" :disabled="loading || bulkForm.adjustments.some(a => !a.product_id)"
                            class="gap-2">
                            <RefreshCwIcon class="h-4 w-4" />
                            {{ loading ? 'Memproses...' : 'Terapkan Penyesuaian Massal' }}
                        </Button>
                        <Button type="button" variant="outline" @click="handleCancel" :disabled="loading">
                            Batal
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
