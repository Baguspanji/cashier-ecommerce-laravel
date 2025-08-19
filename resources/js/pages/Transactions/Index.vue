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

const { loading, visitShow, visitPOS, visitIndex } = useTransactions()

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

    visitIndex(filters)
})

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
        'cash': { label: 'Tunai', variant: 'default' },
        'debit': { label: 'Debit', variant: 'secondary' },
        'credit': { label: 'Kredit', variant: 'secondary' },
        'e-wallet': { label: 'E-Wallet', variant: 'warning' }
    }
    return methods[method] || { label: method, variant: 'default' as const }
}
</script>

<template>

    <Head title="Transaksi" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Transaksi" description="Kelola dan lihat semua transaksi penjualan">
                <template #actions>
                    <Button variant="outline" as-child>
                        <Link href="/transactions/daily-report">
                        <FileTextIcon class="mr-2 h-4 w-4" />
                        Laporan Harian
                        </Link>
                    </Button>
                    <Button @click="visitPOS()">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Penjualan Baru
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Penjualan</p>
                                <p class="text-2xl font-bold">{{ props.transactions.meta?.total || 0 }}</p>
                            </div>
                            <ReceiptIcon class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Pendapatan Hari Ini</p>
                                <p class="text-2xl font-bold">
                                    {{formatCurrency(props.transactions.data
                                        .filter(t => new Date(t.created_at).toDateString() === new Date().toDateString())
                                        .reduce((sum, t) => sum + parseFloat(t.total_amount), 0)
                                    )}}
                                </p>
                            </div>
                            <DollarSignIcon class="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Rata-rata Penjualan</p>
                                <p class="text-2xl font-bold">
                                    {{formatCurrency(
                                        props.transactions.data.length > 0
                                            ? props.transactions.data.reduce((sum, t) => sum + parseFloat(t.total_amount), 0) /
                                            props.transactions.data.length
                                            : 0
                                    )}}
                                </p>
                            </div>
                            <CalendarIcon class="h-8 w-8 text-purple-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Bulan Ini</p>
                                <p class="text-2xl font-bold">
                                    {{props.transactions.data
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

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-5">
                        <!-- Search -->
                        <div class="grid gap-2">
                            <Label for="search">Cari Transaksi</Label>
                            <div class="relative">
                                <SearchIcon
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Kode Transaksi..."
                                    class="pl-10 h-10" />
                            </div>
                        </div>

                        <!-- Date Filter -->
                        <div class="grid gap-2">
                            <Label for="date">Tanggal</Label>
                            <Input id="date" v-model="date" type="date" class="h-10" />
                        </div>

                        <!-- Status Filter -->
                        <div class="grid">
                            <Label for="status" class="mb-2">Status</Label>
                            <Select v-model="status">
                                <SelectTrigger class="h-10">
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="completed">Selesai</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="cancelled">Dibatalkan</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Clear Filters -->
                        <div class="grid gap-2">
                            <Label>&nbsp;</Label>
                            <Button variant="outline" @click="search = ''; date = ''; status = 'all'" class="h-10">
                                Reset Filter
                            </Button>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2">
                            <Label>Total</Label>
                            <div class="text-2xl font-bold">
                                {{ props.transactions.meta?.total || props.transactions.data.length }} transaksi
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Transactions Table -->
            <Card>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Kode Transaksi
                                    </th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Tanggal & Waktu
                                    </th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Items</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Pembayaran</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Total</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction in props.transactions.data" :key="transaction.id"
                                    class="border-b hover:bg-muted/50" @click="visitShow(transaction.id)">
                                    <td class="py-3 px-4">
                                        <div class="font-mono text-sm font-medium">
                                            #{{ String(transaction.transaction_number).padStart(6, '0') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ formatDate(transaction.created_at) }}</div>
                                            <div class="text-muted-foreground">{{ formatTime(transaction.created_at) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ transaction.items.length }} items</div>
                                            <div class="text-muted-foreground">
                                                {{transaction.items.reduce((sum, item) => sum + item.quantity, 0)}}
                                                pieces
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
                                        <Button variant="ghost" size="sm" @click.stop="visitShow(transaction.id)"
                                            :disabled="loading" class="cursor-pointer">
                                            <EyeIcon class="h-4 w-4 mr-1" />
                                            Lihat
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="!props.transactions.data.length" class="text-center py-12">
                            <ReceiptIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">Belum ada transaksi</h3>
                            <p class="text-muted-foreground mb-4">Mulai berjualan untuk melihat transaksi di sini.</p>
                            <Button @click="visitPOS()">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Buat Penjualan Baru
                            </Button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.transactions.links && props.transactions.links.length > 3"
                        class="flex items-center justify-center space-x-2 mt-6">
                        <template v-for="link in props.transactions.links" :key="link.label">
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
