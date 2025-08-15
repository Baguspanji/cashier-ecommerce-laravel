<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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

const { visitCreate } = useStock()

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

    // Apply filters with 300ms debounce
    setTimeout(() => {
        if (Object.keys(filters).length > 0) {
            // Apply filters logic here
        }
    }, 300)
}, { immediate: false })

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
        'in': 'Stock In',
        'out': 'Stock Out',
        'adjustment': 'Adjustment'
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
    <Head title="Stock Movements" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Stock Movements</h1>
                    <p class="text-sm text-gray-500">View and manage inventory movements</p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-2"
                    >
                        <Link href="/stock/overview">
                            <PackageIcon class="h-4 w-4" />
                            Stock Overview
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        @click="visitCreate()"
                        class="gap-2"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New Adjustment
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <Input
                                    id="search"
                                    v-model="search"
                                    type="text"
                                    placeholder="Search products..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Product Filter -->
                        <div class="space-y-2">
                            <Label for="product">Product</Label>
                            <Select v-model="product">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Products" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Products</SelectItem>
                                    <SelectItem
                                        v-for="prod in getUniqueProducts()"
                                        :key="prod.id"
                                        :value="prod.id.toString()"
                                    >
                                        {{ prod.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Type Filter -->
                        <div class="space-y-2">
                            <Label for="type">Movement Type</Label>
                            <Select v-model="type">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="in">Stock In</SelectItem>
                                    <SelectItem value="out">Stock Out</SelectItem>
                                    <SelectItem value="adjustment">Adjustment</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Date Filter -->
                        <div class="space-y-2">
                            <Label for="date">Date</Label>
                            <Input
                                id="date"
                                v-model="date"
                                type="date"
                            />
                        </div>

                        <!-- Clear Filters -->
                        <div class="flex items-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="search = ''; product = 'all'; type = 'all'; date = ''"
                                class="w-full"
                            >
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Movements</p>
                                <p class="text-2xl font-bold">{{ props.movements.meta.total || 0 }}</p>
                            </div>
                            <PackageIcon class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Stock In</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ props.movements.data.filter(m => m.type === 'in').length }}
                                </p>
                            </div>
                            <TrendingUpIcon class="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Stock Out</p>
                                <p class="text-2xl font-bold text-red-600">
                                    {{ props.movements.data.filter(m => m.type === 'out').length }}
                                </p>
                            </div>
                            <TrendingDownIcon class="h-8 w-8 text-red-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Adjustments</p>
                                <p class="text-2xl font-bold text-yellow-600">
                                    {{ props.movements.data.filter(m => m.type === 'adjustment').length }}
                                </p>
                            </div>
                            <SettingsIcon class="h-8 w-8 text-yellow-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Movements Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Stock Movements</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Date & Time</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Product</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Type</th>
                                    <th class="text-center py-3 px-4 font-medium text-gray-500">Quantity</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Reason</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">User</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="movement in movements.data"
                                    :key="movement.id"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ formatDate(movement.created_at) }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div>
                                            <div class="font-medium">{{ movement.product.name }}</div>
                                            <div class="text-sm text-gray-500">{{ movement.product.category.name }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Badge :variant="getMovementTypeBadge(movement.type).variant" class="gap-1">
                                            <component :is="getMovementTypeIcon(movement.type)" class="h-3 w-3" />
                                            {{ getMovementTypeLabel(movement.type) }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span
                                            class="font-semibold"
                                            :class="{
                                                'text-green-600': movement.type === 'in',
                                                'text-red-600': movement.type === 'out',
                                                'text-yellow-600': movement.type === 'adjustment'
                                            }"
                                        >
                                            {{ movement.type === 'out' ? '-' : '+' }}{{ movement.quantity }} pcs
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-sm">{{ movement.notes || '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1">
                                            <UserIcon class="h-3 w-3 text-gray-400" />
                                            <span class="text-sm">{{ movement.user?.name || 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="gap-1"
                                                disabled
                                            >
                                                <EyeIcon class="h-3 w-3" />
                                                View
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="movements.data.length === 0" class="text-center py-12">
                            <PackageIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No stock movements found</h3>
                            <p class="text-gray-500 mb-4">Start managing inventory to see movements here.</p>
                            <Button @click="visitCreate()" class="gap-2">
                                <PlusIcon class="h-4 w-4" />
                                Create Stock Adjustment
                            </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="movements.links && movements.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in movements.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md border',
                                    link.active
                                        ? 'bg-blue-500 text-white border-blue-500'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                {{ link.label }}
                            </Link>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
