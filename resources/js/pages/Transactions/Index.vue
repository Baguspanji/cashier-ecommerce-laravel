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
import { useTransactions, type Transaction, type TransactionFilters } from '@/composables/useTransactions'
import {
    PlusIcon,
    SearchIcon,
    EyeIcon,
    ReceiptIcon,
    CalendarIcon,
    DollarSignIcon,
    UserIcon,
    FileTextIcon
} from 'lucide-vue-next'

interface Props {
    transactions: {
        data: Transaction[]
        links: any[]
        meta: any
    }
    filters: TransactionFilters
}

const props = defineProps<Props>()

const { loading, visitShow, visitPOS } = useTransactions()

// State management
const search = ref(props.filters.search || '')
const date = ref(props.filters.date || '')
const status = ref(props.filters.status || 'all')

// Watch filters for live updating
watch([search, date, status], ([searchValue, dateValue, statusValue]) => {
    const filters: TransactionFilters = {}

    if (searchValue) filters.search = searchValue
    if (dateValue) filters.date = dateValue
    if (statusValue && statusValue !== 'all') filters.status = statusValue

    // Apply filters with 300ms debounce
    setTimeout(() => {
        if (Object.keys(filters).length > 0) {
            // Apply filters logic here
        }
    }, 300)
}, { immediate: false })

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getPaymentMethodBadge = (method: string) => {
    const methods: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'warning' }> = {
        'cash': { label: 'Cash', variant: 'default' },
        'card': { label: 'Card', variant: 'secondary' },
        'transfer': { label: 'Transfer', variant: 'warning' }
    }
    return methods[method] || { label: method, variant: 'default' as const }
}
</script>

<template>
    <Head title="Transactions" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
                    <p class="text-sm text-gray-500">Manage and view all sales transactions</p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-2"
                    >
                        <Link href="/transactions/daily-report">
                            <FileTextIcon class="h-4 w-4" />
                            Daily Report
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        @click="visitPOS()"
                        class="gap-2"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New Sale
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <Input
                                    id="search"
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by transaction ID..."
                                    class="pl-10"
                                />
                            </div>
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

                        <!-- Status Filter -->
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="status">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Clear Filters -->
                        <div class="flex items-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="search = ''; date = ''; status = 'all'"
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
                                <p class="text-sm font-medium text-gray-500">Total Sales</p>
                                <p class="text-2xl font-bold">{{ props.transactions.meta.total || 0 }}</p>
                            </div>
                            <ReceiptIcon class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Today's Revenue</p>
                                <p class="text-2xl font-bold">
                                    {{ formatCurrency(props.transactions.data
                                        .filter(t => new Date(t.created_at).toDateString() === new Date().toDateString())
                                        .reduce((sum, t) => sum + parseFloat(t.total_amount), 0)
                                    ) }}
                                </p>
                            </div>
                            <DollarSignIcon class="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Average Sale</p>
                                <p class="text-2xl font-bold">
                                    {{ formatCurrency(
                                        props.transactions.data.length > 0
                                            ? props.transactions.data.reduce((sum, t) => sum + parseFloat(t.total_amount), 0) / props.transactions.data.length
                                            : 0
                                    ) }}
                                </p>
                            </div>
                            <CalendarIcon class="h-8 w-8 text-purple-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">This Month</p>
                                <p class="text-2xl font-bold">
                                    {{ props.transactions.data
                                        .filter(t => new Date(t.created_at).getMonth() === new Date().getMonth())
                                        .length
                                    }}
                                </p>
                            </div>
                            <UserIcon class="h-8 w-8 text-orange-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Transactions Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Transactions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Transaction ID</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Date & Time</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Items</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Payment</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Total</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="transaction in transactions.data"
                                    :key="transaction.id"
                                    class="border-b hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4">
                                        <div class="font-mono text-sm font-medium">
                                            #{{ String(transaction.id).padStart(6, '0') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ formatDate(transaction.created_at) }}</div>
                                            <div class="text-gray-500">{{ formatTime(transaction.created_at) }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ transaction.items.length }} items</div>
                                            <div class="text-gray-500">
                                                {{ transaction.items.reduce((sum, item) => sum + item.quantity, 0) }} pieces
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Badge :variant="getPaymentMethodBadge(transaction.payment_method).variant">
                                            {{ getPaymentMethodBadge(transaction.payment_method).label }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold">
                                            {{ formatCurrency(parseFloat(transaction.total_amount)) }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="visitShow(transaction.id)"
                                                :disabled="loading"
                                                class="gap-1"
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
                        <div v-if="transactions.data.length === 0" class="text-center py-12">
                            <ReceiptIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
                            <p class="text-gray-500 mb-4">Start selling to see transactions here.</p>
                            <Button @click="visitPOS()" class="gap-2">
                                <PlusIcon class="h-4 w-4" />
                                Create New Sale
                            </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links && transactions.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex space-x-2">
                            <Link
                                v-for="link in transactions.links"
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
