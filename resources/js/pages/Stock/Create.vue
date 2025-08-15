<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
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
    <Head title="Stock Adjustment" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="visitIndex()"
                        class="gap-2"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Stock
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Stock Adjustment</h1>
                        <p class="text-sm text-gray-500">Adjust inventory levels</p>
                    </div>
                </div>
            </div>

            <!-- Mode Selector -->
            <Card class="mb-6">
                <CardContent class="p-4">
                    <div class="flex items-center gap-4">
                        <Label>Adjustment Type:</Label>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-blue-50 border-blue-300 text-blue-700': mode === 'single' }"
                                @click="mode = 'single'"
                                class="gap-2"
                            >
                                <PackageIcon class="h-4 w-4" />
                                Single Product
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :class="{ 'bg-blue-50 border-blue-300 text-blue-700': mode === 'bulk' }"
                                @click="mode = 'bulk'"
                                class="gap-2"
                            >
                                <SettingsIcon class="h-4 w-4" />
                                Bulk Adjustment
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
                                Single Product Adjustment
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Product Selection -->
                            <div class="space-y-2">
                                <Label for="product_id">Product *</Label>
                                <Select v-model="singleForm.product_id">
                                    <SelectTrigger
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.product_id
                                        }"
                                    >
                                        <SelectValue placeholder="Select product to adjust" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="product in products"
                                            :key="product.id"
                                            :value="product.id.toString()"
                                        >
                                            <div class="flex justify-between items-center w-full">
                                                <div>
                                                    <div class="font-medium">{{ product.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ product.category.name }}</div>
                                                </div>
                                                <div class="text-sm text-gray-500 ml-4">
                                                    Stock: {{ product.current_stock }}
                                                </div>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.product_id" class="text-sm text-red-600">{{ errors.product_id }}</p>
                            </div>

                            <!-- Current Stock Display -->
                            <div v-if="singleForm.product_id" class="p-4 bg-gray-50 rounded-lg">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Current Stock</p>
                                        <p class="text-lg font-semibold">
                                            {{ getProductById(parseInt(singleForm.product_id.toString()))?.current_stock || 0 }} pcs
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Minimum Stock</p>
                                        <p class="text-lg font-semibold">
                                            {{ getProductById(parseInt(singleForm.product_id.toString()))?.minimum_stock || 0 }} pcs
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Adjustment Type -->
                            <div class="space-y-2">
                                <Label for="type">Adjustment Type *</Label>
                                <Select v-model="singleForm.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select adjustment type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="in">Stock In (+)</SelectItem>
                                        <SelectItem value="out">Stock Out (-)</SelectItem>
                                        <SelectItem value="adjustment">Stock Adjustment</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.type" class="text-sm text-red-600">{{ errors.type }}</p>
                            </div>

                            <!-- Quantity -->
                            <div class="space-y-2">
                                <Label for="quantity">Quantity *</Label>
                                <div class="flex items-center gap-2">
                                    <Input
                                        id="quantity"
                                        v-model.number="singleForm.quantity"
                                        type="number"
                                        min="1"
                                        placeholder="Enter quantity"
                                        required
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.quantity
                                        }"
                                        class="flex-1"
                                    />
                                    <span class="text-sm text-gray-500">pcs</span>
                                </div>
                                <p v-if="errors.quantity" class="text-sm text-red-600">{{ errors.quantity }}</p>
                            </div>

                            <!-- New Stock Preview -->
                            <div v-if="singleForm.product_id && singleForm.quantity" class="p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-600 mb-2">After Adjustment:</p>
                                <p class="text-xl font-bold text-blue-700">
                                    {{
                                        (() => {
                                            const product = getProductById(parseInt(singleForm.product_id.toString()))
                                            const currentStock = product?.current_stock || 0
                                            const quantity = singleForm.quantity || 0

                                            if (singleForm.type === 'in') return currentStock + quantity
                                            if (singleForm.type === 'out') return Math.max(0, currentStock - quantity)
                                            return quantity // For adjustment type, quantity is new stock
                                        })()
                                    }} pcs
                                </p>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <Label for="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    v-model="singleForm.notes"
                                    rows="3"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Enter reason for adjustment (optional)"
                                />
                                <p v-if="errors.notes" class="text-sm text-red-600">{{ errors.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <Button
                            type="submit"
                            :disabled="loading"
                            class="gap-2"
                        >
                            <SaveIcon class="h-4 w-4" />
                            {{ loading ? 'Processing...' : 'Save Adjustment' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="handleCancel"
                            :disabled="loading"
                        >
                            Cancel
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
                                    Bulk Stock Adjustment
                                </CardTitle>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addBulkAdjustment"
                                    class="gap-2"
                                >
                                    <PlusIcon class="h-4 w-4" />
                                    Add Product
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Adjustment Rows -->
                                <div
                                    v-for="(adjustment, index) in bulkForm.adjustments"
                                    :key="index"
                                    class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border rounded-lg"
                                >
                                    <!-- Product -->
                                    <div class="md:col-span-2">
                                        <Label :for="`product_${index}`">Product</Label>
                                        <Select v-model="adjustment.product_id">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select product" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="product in products"
                                                    :key="product.id"
                                                    :value="product.id.toString()"
                                                >
                                                    {{ product.name }} ({{ product.current_stock }} pcs)
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <!-- Current Stock -->
                                    <div>
                                        <Label>Current Stock</Label>
                                        <Input
                                            :value="getProductById(parseInt(adjustment.product_id.toString()))?.current_stock || 0"
                                            disabled
                                            class="bg-gray-50"
                                        />
                                    </div>

                                    <!-- New Stock -->
                                    <div>
                                        <Label :for="`new_stock_${index}`">New Stock</Label>
                                        <Input
                                            :id="`new_stock_${index}`"
                                            v-model.number="adjustment.new_stock"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                        />
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-end">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="removeBulkAdjustment(index)"
                                            :disabled="bulkForm.adjustments.length === 1"
                                            class="gap-2 w-full"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                            Remove
                                        </Button>
                                    </div>
                                </div>

                                <!-- Global Notes -->
                                <div class="space-y-2 pt-4 border-t">
                                    <Label for="bulk_notes">Global Notes</Label>
                                    <textarea
                                        id="bulk_notes"
                                        v-model="bulkForm.notes"
                                        rows="3"
                                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Enter reason for bulk adjustment (optional)"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <Button
                            type="submit"
                            :disabled="loading || bulkForm.adjustments.some(a => !a.product_id)"
                            class="gap-2"
                        >
                            <RefreshCwIcon class="h-4 w-4" />
                            {{ loading ? 'Processing...' : 'Apply Bulk Adjustment' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="handleCancel"
                            :disabled="loading"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
