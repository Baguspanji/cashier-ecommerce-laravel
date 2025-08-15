<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { useProducts, type Product, type ProductFilters } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import {
    PlusIcon,
    SearchIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    AlertTriangleIcon
} from 'lucide-vue-next'

interface Props {
    products: {
        data: Product[]
        links: any[]
        meta: any
    }
    categories: Category[]
    filters: ProductFilters
}

const props = defineProps<Props>()

const { loading, destroy, toggleStatus, visitIndex, visitShow, visitCreate, visitEdit } = useProducts()

// State management
const search = ref(props.filters.search || '')
const categoryId = ref(props.filters.category_id?.toString() || 'all')
const status = ref(props.filters.status || 'all')

// Watch filters for live updating
watch([search, categoryId, status], ([searchValue, categoryValue, statusValue]) => {
    const filters: ProductFilters = {}

    if (searchValue) filters.search = searchValue
    if (categoryValue !== 'all') filters.category_id = parseInt(categoryValue)
    if (statusValue !== 'all') filters.status = statusValue

    visitIndex(filters)
})

// Computed
const formatPrice = (price: string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(parseFloat(price))
}

const getStockStatus = (product: Product) => {
    if (product.current_stock === 0) {
        return { label: 'Habis', variant: 'destructive', icon: XCircleIcon }
    } else if (product.current_stock <= product.minimum_stock) {
        return { label: 'Stok Rendah', variant: 'warning', icon: AlertTriangleIcon }
    } else {
        return { label: 'Tersedia', variant: 'success', icon: CheckCircleIcon }
    }
}

// Functions
const handleDelete = (product: Product) => {
    destroy(product.id)
}

const handleToggleStatus = (product: Product) => {
    toggleStatus(product.id)
}

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Produk', href: route('products.index') },
]
</script>

<template>
    <Head title="Produk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Produk</h1>
                    <p class="text-muted-foreground">
                        Kelola produk dan inventori toko Anda
                    </p>
                </div>
                <Button @click="visitCreate">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Tambah Produk
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="grid gap-2">
                            <Label for="search">Cari Produk</Label>
                            <div class="relative">
                                <SearchIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="search"
                                    v-model="search"
                                    placeholder="Nama atau deskripsi..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="grid gap-2">
                            <Label for="category">Kategori</Label>
                            <Select v-model="categoryId">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kategori</SelectItem>
                                    <SelectItem v-for="category in props.categories" :key="category.id" :value="category.id.toString()">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status Filter -->
                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="inactive">Non-aktif</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2">
                            <Label>Total</Label>
                            <div class="text-2xl font-bold">
                                {{ props.products.meta.total || 0 }} produk
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="product in props.products.data" :key="product.id" class="hover:shadow-md transition-shadow">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-base leading-tight">{{ product.name }}</CardTitle>
                                <p class="text-xs text-muted-foreground">{{ product.category.name }}</p>
                                <div class="flex items-center space-x-2">
                                    <span :class="{
                                        'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium': true,
                                        'bg-green-100 text-green-800': getStockStatus(product).variant === 'success',
                                        'bg-yellow-100 text-yellow-800': getStockStatus(product).variant === 'warning',
                                        'bg-red-100 text-red-800': getStockStatus(product).variant === 'destructive'
                                    }">
                                        <component :is="getStockStatus(product).icon" class="mr-1 h-3 w-3" />
                                        {{ getStockStatus(product).label }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="visitShow(product.id)"
                                    class="h-8 w-8"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="visitEdit(product.id)"
                                    class="h-8 w-8"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="handleDelete(product)"
                                    class="h-8 w-8"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div class="text-lg font-semibold">{{ formatPrice(product.price) }}</div>
                            <div class="text-sm text-muted-foreground">
                                Stok: {{ product.current_stock }} / Min: {{ product.minimum_stock }}
                            </div>
                            <div class="flex items-center justify-between">
                                <span :class="{
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium': true,
                                    'bg-green-100 text-green-800': product.is_active,
                                    'bg-gray-100 text-gray-800': !product.is_active
                                }">
                                    {{ product.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="handleToggleStatus(product)"
                                    :disabled="loading"
                                >
                                    {{ product.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-if="!props.products.data.length" class="p-8 text-center">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Belum ada produk</h3>
                    <p class="text-muted-foreground">
                        Mulai dengan menambahkan produk pertama Anda
                    </p>
                    <Button @click="visitCreate" class="mt-4">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Tambah Produk Pertama
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
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>
