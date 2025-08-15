<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { useProducts, type Product } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    PackageIcon,
    TagIcon,
    CalendarIcon,
    DollarSignIcon
} from 'lucide-vue-next'

interface Props {
    product: Product
    category: Category
}

defineProps<Props>()

const { loading, destroy, toggleStatus, visitEdit, visitIndex } = useProducts()

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getStockStatusColor = (stock: number, minStock: number) => {
    if (stock <= 0) return 'destructive'
    if (stock <= minStock) return 'warning'
    return 'default'
}

const getStockStatusText = (stock: number, minStock: number) => {
    if (stock <= 0) return 'Out of Stock'
    if (stock <= minStock) return 'Low Stock'
    return 'In Stock'
}
</script>

<template>
    <Head :title="`Product - ${product.name}`" />

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
                        Back to Products
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ product.name }}</h1>
                        <p class="text-sm text-gray-500">Product Details</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="visitEdit(product.id)"
                        :disabled="loading"
                        class="gap-2"
                    >
                        <PencilIcon class="h-4 w-4" />
                        Edit
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="destroy(product.id)"
                        :disabled="loading"
                        class="gap-2"
                    >
                        <TrashIcon class="h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Product Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Product Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Product Name</label>
                                    <p class="text-lg font-semibold">{{ product.name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Price</label>
                                    <p class="text-lg font-mono">{{ formatCurrency(parseFloat(product.price)) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Category</label>
                                    <div class="flex items-center gap-2 mt-1">
                                        <TagIcon class="h-4 w-4 text-gray-400" />
                                        <span class="font-medium">{{ category.name }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                    <div class="mt-1">
                                        <Badge
                                            :variant="product.is_active ? 'default' : 'destructive'"
                                            class="gap-1"
                                        >
                                            <CheckCircleIcon v-if="product.is_active" class="h-3 w-3" />
                                            <XCircleIcon v-else class="h-3 w-3" />
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div v-if="product.description">
                                <label class="text-sm font-medium text-gray-500">Description</label>
                                <p class="mt-1 text-gray-700 whitespace-pre-wrap">{{ product.description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <DollarSignIcon class="h-5 w-5" />
                                Pricing
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center p-6 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-600">Product Price</p>
                                <p class="text-3xl font-bold text-blue-700">
                                    {{ formatCurrency(parseFloat(product.price)) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stock Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Stock Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="text-center">
                                <Badge
                                    :variant="getStockStatusColor(product.current_stock, product.minimum_stock)"
                                    class="text-sm px-3 py-1"
                                >
                                    {{ getStockStatusText(product.current_stock, product.minimum_stock) }}
                                </Badge>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Current Stock</span>
                                    <span class="font-semibold">{{ product.current_stock }} pcs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Minimum Stock</span>
                                    <span class="font-semibold">{{ product.minimum_stock }} pcs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Unit</span>
                                    <span class="font-semibold">pcs</span>
                                </div>
                            </div>

                            <!-- Stock Level Progress -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Stock Level</span>
                                    <span>{{ Math.round((product.current_stock / (product.minimum_stock * 3)) * 100) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full transition-all"
                                        :class="{
                                            'bg-red-500': product.current_stock <= 0,
                                            'bg-yellow-500': product.current_stock > 0 && product.current_stock <= product.minimum_stock,
                                            'bg-green-500': product.current_stock > product.minimum_stock
                                        }"
                                        :style="{ width: Math.min(100, Math.max(5, (product.current_stock / (product.minimum_stock * 3)) * 100)) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timestamps -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5" />
                                Timeline
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Created</p>
                                <p class="text-sm">{{ formatDate(product.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Last Updated</p>
                                <p class="text-sm">{{ formatDate(product.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                @click="toggleStatus(product.id)"
                                :disabled="loading"
                            >
                                <component
                                    :is="product.is_active ? XCircleIcon : CheckCircleIcon"
                                    class="h-4 w-4"
                                />
                                {{ product.is_active ? 'Deactivate' : 'Activate' }}
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                as-child
                            >
                                <Link href="/stock/overview">
                                    <PackageIcon class="h-4 w-4" />
                                    View Stock History
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
