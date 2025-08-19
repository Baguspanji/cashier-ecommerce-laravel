<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { type Product } from '@/composables/useProducts'
import { useStock } from '@/composables/useStock'
import {
    ArrowLeftIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    SettingsIcon,
    CalendarIcon,
    UserIcon,
    PackageIcon
} from 'lucide-vue-next'

interface StockMovement {
    id: number
    type: 'in' | 'out' | 'adjustment'
    quantity: number
    previous_stock: number
    new_stock: number
    reference_id?: number
    reference_type?: string
    notes?: string
    user?: {
        id: number
        name: string
        email: string
    }
    created_at: string
    updated_at: string
}

interface Props {
    product: Product
    movements: {
        data: StockMovement[]
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
}

const props = defineProps<Props>()

const { visitOverview } = useStock()

// Computed
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getMovementIcon = (type: string) => {
    switch (type) {
        case 'in':
            return TrendingUpIcon
        case 'out':
            return TrendingDownIcon
        case 'adjustment':
            return SettingsIcon
        default:
            return PackageIcon
    }
}

const getMovementLabel = (type: string) => {
    switch (type) {
        case 'in':
            return 'Stok Masuk'
        case 'out':
            return 'Stok Keluar'
        case 'adjustment':
            return 'Penyesuaian'
        default:
            return 'Pergerakan'
    }
}

const getMovementVariant = (type: string) => {
    switch (type) {
        case 'in':
            return 'default' as const
        case 'out':
            return 'destructive' as const
        case 'adjustment':
            return 'secondary' as const
        default:
            return 'outline' as const
    }
}

const getReferenceTypeLabel = (referenceType?: string) => {
    switch (referenceType) {
        case 'manual':
            return 'Manual'
        case 'initial':
            return 'Stok Awal'
        case 'adjustment':
            return 'Penyesuaian'
        case 'transaction':
            return 'Transaksi'
        default:
            return referenceType || '-'
    }
}

const stockChangeSymbol = (movement: StockMovement) => {
    const change = movement.new_stock - movement.previous_stock
    if (change > 0) return '+'
    if (change < 0) return ''
    return ''
}
</script>

<template>

    <Head :title="`Riwayat Stok - ${props.product.name}`" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader :title="`Riwayat Stok - ${props.product.name}`"
                :description="`Lihat semua pergerakan stok untuk produk ${props.product.name}`">
                <template #actions>
                    <Button variant="outline" @click="visitOverview">
                        <ArrowLeftIcon class="mr-2 h-4 w-4" />
                        Kembali ke Overview
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Product Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <PackageIcon class="mr-2 h-5 w-5" />
                        Informasi Produk
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Nama Produk</div>
                            <div class="font-medium">{{ props.product.name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Kategori</div>
                            <div class="font-medium">{{ props.product.category?.name || 'Tanpa Kategori' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Stok Saat Ini</div>
                            <div class="font-medium text-lg">{{ props.product.current_stock }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Minimum Stok</div>
                            <div class="font-medium">{{ props.product.minimum_stock }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Stock Movements -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center justify-between">
                        <span>Riwayat Pergerakan Stok</span>
                        <span class="text-sm font-normal text-muted-foreground">
                            Total: {{ props.movements?.meta?.total || 0 }} pergerakan
                        </span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div v-for="movement in props.movements.data" :key="movement.id"
                            class="border rounded-lg p-4 hover:shadow-sm transition-shadow">

                            <!-- Movement Header with Timeline -->
                            <div class="flex items-start space-x-4">
                                <!-- Icon with colored background -->
                                <div :class="{
                                    'p-2 rounded-full': true,
                                    'bg-green-100 text-green-600': movement.type === 'in',
                                    'bg-red-100 text-red-600': movement.type === 'out',
                                    'bg-blue-100 text-blue-600': movement.type === 'adjustment'
                                }">
                                    <component :is="getMovementIcon(movement.type)" class="h-4 w-4" />
                                </div>

                                <!-- Main Content -->
                                <div class="flex-1 space-y-2">
                                    <!-- Type and Date -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <Badge :variant="getMovementVariant(movement.type)" class="text-xs">
                                                {{ getMovementLabel(movement.type) }}
                                            </Badge>
                                            <span class="text-xs text-muted-foreground">
                                                {{ getReferenceTypeLabel(movement.reference_type) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-muted-foreground flex items-center">
                                            <CalendarIcon class="mr-1 h-3 w-3" />
                                            {{ formatDate(movement.created_at) }}
                                        </div>
                                    </div>

                                    <!-- Stock Change Visualization -->
                                    <div class="bg-muted/30 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium">Perubahan Stok</span>
                                            <span class="text-sm text-muted-foreground">Qty: {{ movement.quantity }}</span>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <!-- Previous Stock -->
                                            <div class="text-center">
                                                <div class="text-xs text-muted-foreground">Sebelum</div>
                                                <div class="text-lg font-semibold">{{ movement.previous_stock }}</div>
                                            </div>

                                            <!-- Arrow with change -->
                                            <div class="flex-1 flex items-center justify-center">
                                                <div class="flex items-center space-x-2">
                                                    <div class="h-px bg-border flex-1"></div>
                                                    <div :class="{
                                                        'px-2 py-1 rounded text-xs font-medium': true,
                                                        'bg-green-100 text-green-700': movement.new_stock - movement.previous_stock > 0,
                                                        'bg-red-100 text-red-700': movement.new_stock - movement.previous_stock < 0,
                                                        'bg-gray-100 text-gray-700': movement.new_stock - movement.previous_stock === 0
                                                    }">
                                                        {{ stockChangeSymbol(movement) }}{{ movement.new_stock - movement.previous_stock }}
                                                    </div>
                                                    <div class="h-px bg-border flex-1"></div>
                                                </div>
                                            </div>

                                            <!-- New Stock -->
                                            <div class="text-center">
                                                <div class="text-xs text-muted-foreground">Sesudah</div>
                                                <div class="text-lg font-semibold">{{ movement.new_stock }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Details -->
                                    <div v-if="movement.user || movement.reference_id || movement.notes"
                                         class="grid gap-2 md:grid-cols-3 text-sm pt-2 border-t">
                                        <div v-if="movement.user" class="flex items-center space-x-1">
                                            <UserIcon class="h-3 w-3 text-muted-foreground" />
                                            <span class="text-muted-foreground">oleh</span>
                                            <span class="font-medium">{{ movement.user.name }}</span>
                                        </div>
                                        <div v-if="movement.reference_id" class="flex items-center space-x-1">
                                            <span class="text-muted-foreground">Ref:</span>
                                            <span class="font-mono text-xs bg-muted px-1 rounded">{{ movement.reference_id }}</span>
                                        </div>
                                        <div v-if="movement.notes" class="md:col-span-3">
                                            <span class="text-muted-foreground">Catatan:</span>
                                            <span class="ml-1 italic">{{ movement.notes }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!props.movements.data.length" class="text-center py-8">
                            <PackageIcon class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-2 text-lg font-semibold">Belum ada pergerakan stok</h3>
                            <p class="text-muted-foreground">
                                Belum ada catatan pergerakan stok untuk produk ini
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="props.movements?.links && props.movements.links.length > 3"
                class="flex items-center justify-center space-x-2">
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
        </div>
    </AppLayout>
</template>
