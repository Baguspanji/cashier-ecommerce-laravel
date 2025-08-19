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
import { useStock, type StockMovement, type StockFilters } from '@/composables/useStock'
import {
    PlusIcon,
    SearchIcon,
    EyeIcon,
    PackageIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    SettingsIcon,
    UserIcon
} from 'lucide-vue-next'

interface Props {
    movements: {
        data: StockMovement[]
        links: any[]
        meta: any
    }
    filters: StockFilters
}

const props = defineProps<Props>()

const { visitCreate, visitIndex } = useStock()

// State management
const search = ref(props.filters.search || '')
const product = ref(props.filters.product_id?.toString() || 'all')
const type = ref(props.filters.type || 'all')
const date = ref(props.filters.date || '')

// Watch filters for live updating
watch([search, product, type, date], ([searchValue, productValue, typeValue, dateValue]) => {
    const filters: StockFilters = {}

    if (searchValue) filters.search = searchValue
    if (productValue && productValue !== 'all') filters.product_id = parseInt(productValue)
    if (typeValue && typeValue !== 'all') filters.type = typeValue as any
    if (dateValue) filters.date = dateValue

    visitIndex(filters)
})

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getMovementTypeIcon = (type: string) => {
    const icons: Record<string, any> = {
        'in': TrendingUpIcon,
        'out': TrendingDownIcon,
        'adjustment': SettingsIcon
    }
    return icons[type] || PackageIcon
}

const getMovementTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        'in': 'Masuk',
        'out': 'Keluar',
        'adjustment': 'Penyesuaian'
    }
    return labels[type] || type
}

const getMovementTypeBadge = (type: string) => {
    const badges: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'warning' }> = {
        'in': { variant: 'default' },
        'out': { variant: 'destructive' },
        'adjustment': { variant: 'warning' }
    }
    return badges[type] || { variant: 'default' as const }
}

const getUniqueProducts = () => {
    const products = new Map()
    props.movements.data.forEach(movement => {
        if (!products.has(movement.product_id)) {
            products.set(movement.product_id, movement.product)
        }
    })
    return Array.from(products.values())
}
</script>

<template>

    <Head title="Pergerakan Stok" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Pergerakan Stok" description="Lihat dan kelola pergerakan inventori">
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/stock/overview">
                        <PackageIcon class="mr-2 h-4 w-4" />
                        Ringkasan Stok
                        </Link>
                    </Button>
                    <Button @click="visitCreate()">
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
                            <div class="text-2xl font-bold">{{ props.movements.meta?.total || 0 }}</div>
                            <p class="text-sm text-muted-foreground">Total Pergerakan</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/20">
                            <TrendingUpIcon class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{props.movements.data.filter(m => m.type === 'in').length}}
                            </div>
                            <p class="text-sm text-muted-foreground">Stok Masuk</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/20">
                            <TrendingDownIcon class="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{props.movements.data.filter(m => m.type === 'out').length}}
                            </div>
                            <p class="text-sm text-muted-foreground">Stok Keluar</p>
                        </div>
                    </div>
                </Card>

                <Card class="px-5 py-6">
                    <div class="flex items-center gap-4">
                        <div class="p-2 rounded-lg bg-yellow-100 dark:bg-yellow-900/20">
                            <SettingsIcon class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                {{props.movements.data.filter(m => m.type === 'adjustment').length}}
                            </div>
                            <p class="text-sm text-muted-foreground">Penyesuaian</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <!-- Search -->
                        <div class="grid gap-2">
                            <Label for="search">Cari Produk</Label>
                            <div class="relative">
                                <SearchIcon
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Nama produk..." class="pl-10 h-10" />
                            </div>
                        </div>

                        <!-- Product Filter -->
                        <div class="grid2">
                            <Label for="product" class="mb-2">Produk</Label>
                            <Select v-model="product" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Produk" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Produk</SelectItem>
                                    <SelectItem v-for="prod in getUniqueProducts()" :key="prod.id"
                                        :value="prod.id.toString()">
                                        {{ prod.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Type Filter -->
                        <div class="grid">
                            <Label for="type" class="mb-2">Tipe Pergerakan</Label>
                            <Select v-model="type" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua Tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Tipe</SelectItem>
                                    <SelectItem value="in">Masuk</SelectItem>
                                    <SelectItem value="out">Keluar</SelectItem>
                                    <SelectItem value="adjustment">Penyesuaian</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Date Filter -->
                        <div class="grid gap-2">
                            <Label for="date">Tanggal</Label>
                            <Input id="date" v-model="date" type="date" class="h-10" />
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2">
                            <Label>Hasil Filter</Label>
                            <div class="text-2xl font-bold">
                                {{ props.movements.meta?.total || props.movements.data.length }} pergerakan
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Movements Table -->
            <Card>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Tanggal & Waktu
                                    </th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Produk</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Tipe</th>
                                    <th class="text-center py-3 px-4 font-medium text-muted-foreground">Jumlah</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Alasan</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">User</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in props.movements.data" :key="movement.id"
                                    class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ formatDate(movement.created_at) }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <div class="font-medium">{{ movement.product.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ movement.product.category.name
                                            }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Badge :variant="getMovementTypeBadge(movement.type).variant" class="gap-1">
                                            <component :is="getMovementTypeIcon(movement.type)" class="h-3 w-3" />
                                            {{ getMovementTypeLabel(movement.type) }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-semibold" :class="{
                                            'text-green-600': movement.type === 'in',
                                            'text-red-600': movement.type === 'out',
                                            'text-yellow-600': movement.type === 'adjustment'
                                        }">
                                            {{ movement.type === 'out' ? '-' : '+' }}{{ movement.quantity }} pcs
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm">{{ movement.notes || '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1">
                                            <UserIcon class="h-3 w-3 text-muted-foreground" />
                                            <span class="text-sm">{{ movement.user?.name || 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Button variant="ghost" size="sm" disabled>
                                            <EyeIcon class="h-4 w-4 mr-1" />
                                            Lihat
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="!props.movements.data.length" class="text-center py-12">
                            <div class="space-y-2">
                                <PackageIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 class="text-lg font-semibold mb-2">Belum ada pergerakan stok</h3>
                                <p class="text-muted-foreground mb-4">Mulai kelola inventori untuk melihat pergerakan di
                                    sini.</p>
                                <Button @click="visitCreate()">
                                    <PlusIcon class="mr-2 h-4 w-4" />
                                    Buat Penyesuaian Stok
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.movements.links && props.movements.links.length > 3"
                        class="flex items-center justify-center space-x-2 mt-6">
                        <template v-for="link in props.movements.links" :key="link.label">
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
