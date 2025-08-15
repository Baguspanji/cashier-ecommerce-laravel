<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { useStock } from '@/composables/useStock'
import { type Product } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import {
    PackageIcon,
    AlertTriangleIcon,
    XCircleIcon,
    TrendingUpIcon,
    PlusIcon,
    HistoryIcon
} from 'lucide-vue-next'

interface Props {
    products: {
        data: Product[]
        links: any[]
        meta: any
    }
    categories: Category[]
    summary: {
        total_products: number
        low_stock_count: number
        out_of_stock_count: number
        total_stock_value: number
    }
    filters: {
        category_id?: number
        stock_status?: string
    }
}

const props = defineProps<Props>()

const { visitOverview, visitCreate, visitProductMovements } = useStock()

// State management
const categoryId = ref(props.filters.category_id?.toString() || 'all')
const stockStatus = ref(props.filters.stock_status || 'all')

// Watch filters for live updating
watch([categoryId, stockStatus], ([categoryValue, statusValue]) => {
    const filters: any = {}

    if (categoryValue !== 'all') filters.category_id = parseInt(categoryValue)
    if (statusValue !== 'all') filters.stock_status = statusValue

    visitOverview(filters)
})

// Computed
const formatPrice = (price: string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(parseFloat(price))
}

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value)
}

const getStockStatus = (product: Product) => {
    if (product.current_stock === 0) {
        return { label: 'Habis', variant: 'destructive', bgClass: 'bg-red-100 text-red-800' }
    } else if (product.current_stock <= product.minimum_stock) {
        return { label: 'Stok Rendah', variant: 'warning', bgClass: 'bg-yellow-100 text-yellow-800' }
    } else {
        return { label: 'Tersedia', variant: 'success', bgClass: 'bg-green-100 text-green-800' }
    }
}

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Overview Stok', href: route('stock.overview') },
]
</script>

<template>
    <Head title="Overview Stok" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Overview Stok</h1>
                    <p class="text-muted-foreground">
                        Pantau kondisi stok dan inventori toko Anda
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="visitCreate">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Atur Stok
                    </Button>
                    <Button as-child>
                        <Link :href="route('stock.index')">
                            <HistoryIcon class="mr-2 h-4 w-4" />
                            Riwayat Stok
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Produk</CardTitle>
                        <PackageIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.summary.total_products }}</div>
                        <p class="text-xs text-muted-foreground">
                            Produk terdaftar
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Stok Rendah</CardTitle>
                        <AlertTriangleIcon class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">{{ props.summary.low_stock_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            Perlu diperhatikan
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Stok Habis</CardTitle>
                        <XCircleIcon class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ props.summary.out_of_stock_count }}</div>
                        <p class="text-xs text-muted-foreground">
                            Perlu direstock
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Nilai Stok</CardTitle>
                        <TrendingUpIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(props.summary.total_stock_value) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total aset inventori
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <!-- Category Filter -->
                        <div class="grid gap-2">
                            <Label for="category">Kategori</Label>
                            <select
                                id="category"
                                v-model="categoryId"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="all">Semua Kategori</option>
                                <option v-for="category in props.categories" :key="category.id" :value="category.id.toString()">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="grid gap-2">
                            <Label for="status">Status Stok</Label>
                            <select
                                id="status"
                                v-model="stockStatus"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="all">Semua Status</option>
                                <option value="low">Stok Rendah</option>
                                <option value="out">Stok Habis</option>
                            </select>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2">
                            <Label>Hasil Filter</Label>
                            <div class="text-2xl font-bold">
                                {{ props.products.meta.total || 0 }} produk
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Products List -->
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Produk</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div v-for="product in props.products.data" :key="product.id"
                             class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="space-y-1">
                                    <h4 class="font-medium">{{ product.name }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ product.category.name }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="font-medium">{{ formatPrice(product.price) }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        Stok: {{ product.current_stock }} / Min: {{ product.minimum_stock }}
                                    </div>
                                </div>

                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', getStockStatus(product).bgClass]">
                                    {{ getStockStatus(product).label }}
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="visitProductMovements(product.id)"
                                >
                                    <HistoryIcon class="mr-2 h-4 w-4" />
                                    Riwayat
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="!props.products.data.length" class="p-8 text-center">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Tidak ada produk ditemukan</h3>
                    <p class="text-muted-foreground">
                        Sesuaikan filter atau tambahkan produk baru
                    </p>
                    <Button @click="visitCreate" class="mt-4">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Tambah Produk
                    </Button>
                </div>
            </Card>

            <!-- Pagination -->
            <div v-if="props.products.links.length > 3" class="flex items-center justify-center space-x-2">
                <Link
                    v-for="link in props.products.links"
                    :key="link.label"
                    :href="link.url"
                    :class="{
                        'px-3 py-2 text-sm font-medium rounded-md': true,
                        'bg-primary text-primary-foreground': link.active,
                        'bg-secondary text-secondary-foreground hover:bg-secondary/80': !link.active && link.url,
                        'opacity-50 cursor-not-allowed': !link.url
                    }"
                >
                    {{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
