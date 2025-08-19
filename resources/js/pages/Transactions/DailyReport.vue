<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { type Transaction } from '@/composables/useTransactions'
import {
    FileTextIcon,
    CalendarIcon,
    DollarSignIcon,
    ReceiptIcon,
    TrendingUpIcon,
    ShoppingCartIcon,
    PieChartIcon
} from 'lucide-vue-next'

interface Props {
    transactions: Transaction[]
    date?: string
}

const props = defineProps<Props>()

// State
const selectedDate = ref(props.date || new Date().toISOString().split('T')[0])
const reportType = ref('summary')

// Computed values
const filteredTransactions = computed(() => {
    return props.transactions.filter(transaction => {
        const transactionDate = new Date(transaction.created_at).toISOString().split('T')[0]
        return transactionDate === selectedDate.value && transaction.status === 'completed'
    })
})

const dailyStats = computed(() => {
    const transactions = filteredTransactions.value
    const totalSales = transactions.length
    const totalRevenue = transactions.reduce((sum, t) => sum + parseFloat(t.total_amount), 0)
    const totalItems = transactions.reduce((sum, t) => sum + t.items.length, 0)
    const totalQuantity = transactions.reduce((sum, t) =>
        sum + t.items.reduce((itemSum, item) => itemSum + item.quantity, 0), 0
    )
    const averageSale = totalSales > 0 ? totalRevenue / totalSales : 0

    // Payment methods breakdown
    const paymentMethods = transactions.reduce((acc, t) => {
        acc[t.payment_method] = (acc[t.payment_method] || 0) + parseFloat(t.total_amount)
        return acc
    }, {} as Record<string, number>)

    // Hourly breakdown
    const hourlyBreakdown = transactions.reduce((acc, t) => {
        const hour = new Date(t.created_at).getHours()
        const hourKey = `${hour}:00`
        if (!acc[hourKey]) {
            acc[hourKey] = { sales: 0, revenue: 0 }
        }
        acc[hourKey].sales += 1
        acc[hourKey].revenue += parseFloat(t.total_amount)
        return acc
    }, {} as Record<string, { sales: number; revenue: number }>)

    // Top products
    const productStats = transactions.flatMap(t => t.items).reduce((acc, item) => {
        if (!acc[item.product_name]) {
            acc[item.product_name] = {
                name: item.product_name,
                quantity: 0,
                revenue: 0,
                category: item.product.category.name
            }
        }
        acc[item.product_name].quantity += item.quantity
        acc[item.product_name].revenue += parseFloat(item.subtotal)
        return acc
    }, {} as Record<string, { name: string; quantity: number; revenue: number; category: string }>)

    const topProducts = Object.values(productStats)
        .sort((a, b) => b.revenue - a.revenue)
        .slice(0, 10)

    return {
        totalSales,
        totalRevenue,
        totalItems,
        totalQuantity,
        averageSale,
        paymentMethods,
        hourlyBreakdown,
        topProducts
    }
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
        month: 'long',
        day: 'numeric'
    })
}

const getPaymentMethodLabel = (method: string) => {
    const labels: Record<string, string> = {
        'cash': 'Tunai',
        'debit': 'Kartu Debit',
        'credit': 'Kartu Kredit',
        'e-wallet': 'E-Wallet'
    }
    return labels[method] || method
}

// Watch for date changes
watch(selectedDate, () => {
    // Reload data when date changes
})
</script>

<template>

    <Head title="Laporan Harian" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Laporan Harian"
                :description="`Laporan penjualan untuk tanggal ${formatDate(selectedDate)}`">
                <template #actions>
                    <Button as-child>
                        <Link href="/transactions">
                        <FileTextIcon class="mr-2 h-4 w-4" />
                        Laporan Transaksi
                        </Link>
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
                                <p class="text-2xl font-bold">{{ dailyStats.totalSales }}</p>
                            </div>
                            <ReceiptIcon class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Pendapatan</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(dailyStats.totalRevenue) }}</p>
                            </div>
                            <DollarSignIcon class="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Item Terjual</p>
                                <p class="text-2xl font-bold">{{ dailyStats.totalQuantity }}</p>
                            </div>
                            <ShoppingCartIcon class="h-8 w-8 text-purple-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Rata-rata Penjualan</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(dailyStats.averageSale) }}</p>
                            </div>
                            <TrendingUpIcon class="h-8 w-8 text-orange-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="date">Tanggal Laporan</Label>
                            <Input id="date" v-model="selectedDate" type="date" class="h-10" />
                        </div>

                        <div class="grid">
                            <Label for="report-type" class="mb-2">Jenis Laporan</Label>
                            <Select v-model="reportType" class="h-10">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih jenis laporan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="summary">Ringkasan</SelectItem>
                                    <SelectItem value="detailed">Detail</SelectItem>
                                    <SelectItem value="products">Produk</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label>Total Transaksi</Label>
                            <div class="text-2xl font-bold">
                                {{ dailyStats.totalSales }} transaksi
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Payment Methods -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <PieChartIcon class="h-5 w-5" />
                            Metode Pembayaran
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="(amount, method) in dailyStats.paymentMethods" :key="method"
                                class="flex justify-between items-center">
                                <span class="font-medium">{{ getPaymentMethodLabel(method) }}</span>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(amount) }}</div>
                                    <div class="text-sm text-muted-foreground">
                                        {{ Math.round((amount / dailyStats.totalRevenue) * 100) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Hourly Breakdown -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CalendarIcon class="h-5 w-5" />
                            Penjualan Per Jam
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="(data, hour) in dailyStats.hourlyBreakdown" :key="hour"
                                class="grid grid-cols-3 gap-2 text-sm">
                                <span class="font-medium">{{ hour }}</span>
                                <span class="text-center">{{ data.sales }} transaksi</span>
                                <span class="text-right font-semibold">{{ formatCurrency(data.revenue) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Products -->
            <Card>
                <CardHeader>
                    <CardTitle>Produk Terlaris</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Peringkat</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Produk</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Kategori</th>
                                    <th class="text-center py-3 px-4 font-medium text-muted-foreground">Qty Terjual</th>
                                    <th class="text-right py-3 px-4 font-medium text-muted-foreground">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in dailyStats.topProducts" :key="product.name"
                                    class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <Badge :variant="index < 3 ? 'default' : 'secondary'">
                                            #{{ index + 1 }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4 font-medium">{{ product.name }}</td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ product.category }}</td>
                                    <td class="py-3 px-4 text-center font-semibold">{{ product.quantity }} pcs</td>
                                    <td class="py-3 px-4 text-right font-semibold">{{ formatCurrency(product.revenue) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="dailyStats.topProducts.length === 0" class="text-center py-12">
                            <ShoppingCartIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">Tidak ada penjualan</h3>
                            <p class="text-muted-foreground">Tidak ditemukan transaksi untuk tanggal yang dipilih.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Detailed Transactions -->
            <Card v-if="reportType === 'detailed'">
                <CardHeader>
                    <CardTitle>Semua Transaksi</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Waktu</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">No. Transaksi</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Items</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Pembayaran</th>
                                    <th class="text-right py-3 px-4 font-medium text-muted-foreground">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction in filteredTransactions" :key="transaction.id"
                                    class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        {{ new Date(transaction.created_at).toLocaleTimeString('id-ID', {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono">
                                        #{{ String(transaction.transaction_number).padStart(6, '0') }}
                                    </td>
                                    <td class="py-3 px-4">{{ transaction.items.length }} items</td>
                                    <td class="py-3 px-4">
                                        <Badge>{{ getPaymentMethodLabel(transaction.payment_method) }}</Badge>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold">
                                        {{ formatCurrency(parseFloat(transaction.total_amount)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="filteredTransactions.length === 0" class="text-center py-12">
                            <ReceiptIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">Tidak ada transaksi</h3>
                            <p class="text-muted-foreground">Tidak ditemukan transaksi untuk tanggal yang dipilih.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    .space-y-6>*+* {
        margin-top: 0 !important;
    }

    .space-y-6 {
        margin: 0 !important;
        padding: 20px !important;
    }

    button {
        display: none !important;
    }

    .overflow-y-auto {
        overflow-y: visible !important;
        max-height: none !important;
    }
}
</style>
