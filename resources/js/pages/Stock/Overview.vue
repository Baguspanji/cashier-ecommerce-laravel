<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
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
    HistoryIcon,
    CheckCircleIcon
} from 'lucide-vue-next'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

interface Props {
    products: {
        data: Product[]
        links?: {
            url: string | null
            label: string
            active: boolean
        }[]
        meta?: {
            total: number
            per_page: number
            current_page: number
            last_page: number
            from: number | null
            to: number | null
            path: string
            has_more_pages: boolean
        }
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
        return { label: 'Habis', variant: 'destructive' as const, bgClass: 'bg-red-100 text-red-800', icon: XCircleIcon }
    } else if (product.current_stock <= product.minimum_stock) {
        return { label: 'Stok Rendah', variant: 'warning' as const, bgClass: 'bg-yellow-100 text-yellow-800', icon: AlertTriangleIcon }
    } else {
        return { label: 'Tersedia', variant: 'default' as const, bgClass: 'bg-green-100 text-green-800', icon: CheckCircleIcon }
    }
}
</script>

<template>

    <Head title="Overview Stok" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Overview Stok" description="Pantau kondisi stok dan inventori toko Anda">
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link :href="route('stock.index')">
                        <HistoryIcon class="mr-2 h-4 w-4" />
                        Riwayat Stok
                        </Link>
                    </Button>
                    <Button @click="visitCreate">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Atur Stok
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/20">
                            <PackageIcon class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ props.summary.total_products }}</div>
                            <p class="text-sm text-muted-foreground">Total Produk</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-yellow-100 dark:bg-yellow-900/20">
                            <AlertTriangleIcon class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{
                                props.summary.low_stock_count }}</div>
                            <p class="text-sm text-muted-foreground">Stok Rendah</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/20">
                            <XCircleIcon class="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{
                                props.summary.out_of_stock_count }}</div>
                            <p class="text-sm text-muted-foreground">Stok Habis</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/20">
                            <TrendingUpIcon class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ formatCurrency(props.summary.total_stock_value) }}</div>
                            <p class="text-sm text-muted-foreground">Nilai Stok</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <!-- Category Filter -->
                        <div class="grid gap-2">
                            <Label for="category">Kategori</Label>
                            <Select v-model="categoryId" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kategori</SelectItem>
                                    <SelectItem v-for="category in props.categories" :key="category.id"
                                        :value="category.id.toString()">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status Filter -->
                        <div class="grid gap-2">
                            <Label for="status">Status Stok</Label>
                            <Select id="status" v-model="stockStatus" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="low">Stok Rendah</SelectItem>
                                    <SelectItem value="out">Stok Habis</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2">
                            <Label>Hasil Filter</Label>
                            <div class="text-2xl font-bold">
                                {{ props.products.meta?.total || 0 }} Produk
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <Card v-for="product in props.products.data" :key="product.id"
                    class="hover:shadow-md transition-shadow">
                    <CardContent>
                        <div class="space-y-2">
                            <!-- Header with product info -->
                            <div class="space-y-1">
                                <h4 class="font-medium text-sm leading-tight truncate">{{ product.name }}</h4>
                                <p class="text-xs text-muted-foreground truncate">
                                    {{ product.category?.name || 'Tanpa Kategori' }}
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="text-sm font-semibold">{{ formatPrice(product.price) }}</div>

                            <!-- Stock status badge -->
                            <div class="flex items-center justify-between">
                                <Badge :variant="getStockStatus(product).variant" class="text-xs">
                                    <component :is="getStockStatus(product).icon" class="mr-1 h-2 w-2" />
                                    {{ getStockStatus(product).label }}
                                </Badge>
                            </div>

                            <!-- Stock info -->
                            <div class="text-xs text-muted-foreground">
                                Stok: {{ product.current_stock }} / Min: {{ product.minimum_stock }}
                            </div>

                            <!-- Actions -->
                            <div class="pt-2">
                                <Button variant="outline" size="sm" @click="visitProductMovements(product.id)"
                                    class="w-full">
                                    <HistoryIcon class="mr-2 h-4 w-4" />
                                    Lihat Riwayat
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

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
            <div v-if="props.products?.links && props.products.links.length > 3"
                class="flex items-center justify-center space-x-2">
                <template v-for="link in props.products.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" :class="{
                        'px-3 py-2 text-sm font-medium rounded-md': true,
                        'bg-primary text-primary-foreground': link.active,
                        'bg-secondary text-secondary-foreground hover:bg-secondary/80': !link.active
                    }">
                    {{ link.label.replace('pagination.previous', '«').replace('pagination.next', '»') }}
                    </Link>
                    <span v-else
                        class="px-3 py-2 text-sm font-medium rounded-md opacity-50 cursor-not-allowed bg-secondary text-secondary-foreground">
                        {{ link.label.replace('pagination.previous', '«').replace('pagination.next', '»') }}
                    </span>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
