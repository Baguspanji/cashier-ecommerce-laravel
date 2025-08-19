<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
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
    filters: ProductFilters
}

const props = defineProps<Props>()

const { destroy, visitIndex, visitShow, visitCreate, visitEdit } = useProducts()

// State management
const search = ref(props.filters.search || '')
const categoryId = ref(props.filters.category_id?.toString() || 'all')
const status = ref(props.filters.status || 'all')

// Delete dialog state
const isDeleteDialogOpen = ref(false)
const productToDelete = ref<Product | null>(null)

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
        return { label: 'Habis', variant: 'destructive' as const, icon: XCircleIcon }
    } else if (product.current_stock <= product.minimum_stock) {
        return { label: 'Stok Rendah', variant: 'warning' as const, icon: AlertTriangleIcon }
    } else {
        return { label: 'Tersedia', variant: 'default' as const, icon: CheckCircleIcon }
    }
}

// Functions
const handleDelete = (product: Product) => {
    productToDelete.value = product
    isDeleteDialogOpen.value = true
}

const confirmDelete = () => {
    if (productToDelete.value) {
        destroy(productToDelete.value.id)
        isDeleteDialogOpen.value = false
        productToDelete.value = null
    }
}

const cancelDelete = () => {
    isDeleteDialogOpen.value = false
    productToDelete.value = null
}
</script>

<template>

    <Head title="Produk" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Produk" description="Kelola produk dan inventori toko Anda">
                <template #actions>
                    <Button @click="visitCreate">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Tambah Produk
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="grid gap-2">
                            <Label for="search">Cari Produk</Label>
                            <div class="relative">
                                <SearchIcon
                                    class="absolute left-3 top-[42%] h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Nama atau deskripsi..."
                                    class="pl-10 h-10" />
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="grid">
                            <Label for="category" class="mb-2">Kategori</Label>
                            <Select v-model="categoryId" data-testid="category-filter" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Kategori</SelectItem>
                                    <SelectItem v-for="category in props.categories" :key="category.id"
                                        :value="category.id">
                                        {{ category.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status Filter -->
                        <div class="grid">
                            <Label for="status" class="mb-2">Status</Label>
                            <Select v-model="status" data-testid="status-filter" class="h-10">
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
                                {{ props.products?.meta?.total || 0 }} produk
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Products Grid -->
            <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <Card v-for="product in props.products?.data || []" :key="product.id"
                    class="hover:shadow-md transition-shadow">
                    <CardContent>
                        <div class="space-y-2">
                            <!-- Header with actions -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-sm leading-tight truncate">{{ product.name }}</h4>
                                    <p class="text-xs text-muted-foreground truncate">{{ product.category?.name ||
                                        'Tanpa Kategori'
                                    }}</p>
                                </div>
                                <div class="flex space-x-1 ml-2">
                                    <Button variant="ghost" size="icon" @click="visitShow(product.id)" class="h-6 w-6"
                                        :data-testid="`view-product-${product.id}`">
                                        <EyeIcon class="h-3 w-3" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="visitEdit(product.id)" class="h-6 w-6"
                                        :data-testid="`edit-product-${product.id}`">
                                        <PencilIcon class="h-3 w-3" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="handleDelete(product)" class="h-6 w-6"
                                        :data-testid="`delete-product-${product.id}`">
                                        <TrashIcon class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Price and stock -->
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold">{{ formatPrice(product.price) }}</div>
                                <Badge :variant="getStockStatus(product).variant" class="text-xs">
                                    <component :is="getStockStatus(product).icon" class="mr-1 h-2 w-2" />
                                    {{ getStockStatus(product).label }}
                                </Badge>
                            </div>

                            <!-- Stock info -->
                            <div class="text-xs text-muted-foreground">
                                Stok: {{ product.current_stock }} / Min: {{ product.minimum_stock }}
                            </div>

                            <!-- Status and toggle -->
                            <div class="flex items-center justify-between">
                                <Badge :variant="product.is_active ? 'default' : 'secondary'" class="text-xs">
                                    {{ product.is_active ? 'Aktif' : 'Non-aktif' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-if="!props.products?.data?.length" class="p-8 text-center">
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

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Hapus Produk</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus produk <strong>{{ productToDelete?.name }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                    <Button type="button" variant="outline" @click="cancelDelete">
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" @click="confirmDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
