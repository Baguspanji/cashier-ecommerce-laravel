<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
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
    ArrowLeftIcon,
    PrinterIcon,
    DownloadIcon,
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
        'cash': 'Cash',
        'debit': 'Debit Card',
        'credit': 'Credit Card',
        'e-wallet': 'E-Wallet'
    }
    return labels[method] || method
}

const printReport = () => {
    window.print()
}

const exportReport = () => {
    // This would typically export to CSV or PDF
    console.log('Export report functionality would be implemented here')
}

// Watch for date changes
watch(selectedDate, () => {
    // Reload data when date changes
})
</script>

<template>
    <Head title="Daily Sales Report" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        as-child
                        class="gap-2"
                    >
                        <Link href="/transactions">
                            <ArrowLeftIcon class="h-4 w-4" />
                            Back to Transactions
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Daily Sales Report</h1>
                        <p class="text-sm text-gray-500">{{ formatDate(selectedDate) }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="exportReport"
                        class="gap-2"
                    >
                        <DownloadIcon class="h-4 w-4" />
                        Export
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="printReport"
                        class="gap-2"
                    >
                        <PrinterIcon class="h-4 w-4" />
                        Print
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card class="mb-6">
                <CardContent class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="date">Report Date</Label>
                            <Input
                                id="date"
                                v-model="selectedDate"
                                type="date"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="report-type">Report Type</Label>
                            <Select v-model="reportType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select report type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="summary">Summary</SelectItem>
                                    <SelectItem value="detailed">Detailed</SelectItem>
                                    <SelectItem value="products">Products</SelectItem>
                                </SelectContent>
                            </Select>
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
                                <p class="text-2xl font-bold">{{ dailyStats.totalSales }}</p>
                            </div>
                            <ReceiptIcon class="h-8 w-8 text-blue-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(dailyStats.totalRevenue) }}</p>
                            </div>
                            <DollarSignIcon class="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Items Sold</p>
                                <p class="text-2xl font-bold">{{ dailyStats.totalQuantity }}</p>
                            </div>
                            <ShoppingCartIcon class="h-8 w-8 text-purple-500" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Average Sale</p>
                                <p class="text-2xl font-bold">{{ formatCurrency(dailyStats.averageSale) }}</p>
                            </div>
                            <TrendingUpIcon class="h-8 w-8 text-orange-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Payment Methods -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <PieChartIcon class="h-5 w-5" />
                            Payment Methods
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="(amount, method) in dailyStats.paymentMethods"
                                :key="method"
                                class="flex justify-between items-center"
                            >
                                <span class="font-medium">{{ getPaymentMethodLabel(method) }}</span>
                                <div class="text-right">
                                    <div class="font-semibold">{{ formatCurrency(amount) }}</div>
                                    <div class="text-sm text-gray-500">
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
                            Hourly Sales
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div
                                v-for="(data, hour) in dailyStats.hourlyBreakdown"
                                :key="hour"
                                class="grid grid-cols-3 gap-2 text-sm"
                            >
                                <span class="font-medium">{{ hour }}</span>
                                <span class="text-center">{{ data.sales }} sales</span>
                                <span class="text-right font-semibold">{{ formatCurrency(data.revenue) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Products -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Top Selling Products</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 font-medium text-gray-500">Rank</th>
                                    <th class="text-left py-2 font-medium text-gray-500">Product</th>
                                    <th class="text-left py-2 font-medium text-gray-500">Category</th>
                                    <th class="text-center py-2 font-medium text-gray-500">Qty Sold</th>
                                    <th class="text-right py-2 font-medium text-gray-500">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(product, index) in dailyStats.topProducts"
                                    :key="product.name"
                                    class="border-b"
                                >
                                    <td class="py-3">
                                        <Badge :variant="index < 3 ? 'default' : 'secondary'">
                                            #{{ index + 1 }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 font-medium">{{ product.name }}</td>
                                    <td class="py-3 text-gray-600">{{ product.category }}</td>
                                    <td class="py-3 text-center font-semibold">{{ product.quantity }} pcs</td>
                                    <td class="py-3 text-right font-semibold">{{ formatCurrency(product.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="dailyStats.topProducts.length === 0" class="text-center py-8">
                            <ShoppingCartIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No sales found</h3>
                            <p class="text-gray-500">No transactions found for the selected date.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Detailed Transactions -->
            <Card v-if="reportType === 'detailed'">
                <CardHeader>
                    <CardTitle>All Transactions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 font-medium text-gray-500">Time</th>
                                    <th class="text-left py-2 font-medium text-gray-500">Transaction #</th>
                                    <th class="text-left py-2 font-medium text-gray-500">Items</th>
                                    <th class="text-left py-2 font-medium text-gray-500">Payment</th>
                                    <th class="text-right py-2 font-medium text-gray-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="transaction in filteredTransactions"
                                    :key="transaction.id"
                                    class="border-b"
                                >
                                    <td class="py-2">
                                        {{ new Date(transaction.created_at).toLocaleTimeString('id-ID', {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </td>
                                    <td class="py-2 font-mono">{{ transaction.transaction_number }}</td>
                                    <td class="py-2">{{ transaction.items.length }} items</td>
                                    <td class="py-2">{{ getPaymentMethodLabel(transaction.payment_method) }}</td>
                                    <td class="py-2 text-right font-semibold">
                                        {{ formatCurrency(parseFloat(transaction.total_amount)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    .container {
        max-width: none !important;
        margin: 0 !important;
        padding: 20px !important;
    }

    .flex.items-center.justify-between.mb-6 {
        display: none !important;
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
