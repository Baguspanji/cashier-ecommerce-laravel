<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { useTransactions, type Transaction } from '@/composables/useTransactions'
import {
    ArrowLeftIcon,
    PrinterIcon,
    ReceiptIcon,
    UserIcon,
    CalendarIcon,
    DollarSignIcon,
    CreditCardIcon,
    PackageIcon,
    FileTextIcon
} from 'lucide-vue-next'

interface Props {
    transaction: Transaction
}

defineProps<Props>()

const { visitIndex } = useTransactions()

const formatCurrency = (amount: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(typeof amount === 'string' ? parseFloat(amount) : amount)
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getPaymentMethodIcon = (method: string) => {
    const icons: Record<string, any> = {
        'cash': DollarSignIcon,
        'debit': CreditCardIcon,
        'credit': CreditCardIcon,
        'e-wallet': CreditCardIcon
    }
    return icons[method] || CreditCardIcon
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

const getStatusBadge = (status: string) => {
    const statuses: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'warning' }> = {
        'completed': { label: 'Completed', variant: 'default' },
        'pending': { label: 'Pending', variant: 'warning' },
        'cancelled': { label: 'Cancelled', variant: 'destructive' }
    }
    return statuses[status] || { label: status, variant: 'default' as const }
}

const printReceipt = () => {
    window.print()
}
</script>

<template>
    <Head :title="`Transaction #${transaction.transaction_number}`" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="visitIndex()"
                        class="gap-2"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Transactions
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Transaction #{{ transaction.transaction_number }}
                        </h1>
                        <p class="text-sm text-gray-500">Transaction Details</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="printReceipt"
                        class="gap-2"
                    >
                        <PrinterIcon class="h-4 w-4" />
                        Print Receipt
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Transaction Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Transaction Summary -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ReceiptIcon class="h-5 w-5" />
                                Transaction Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Transaction Number</label>
                                    <p class="text-lg font-mono font-semibold">{{ transaction.transaction_number }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                    <div class="mt-1">
                                        <Badge :variant="getStatusBadge(transaction.status).variant">
                                            {{ getStatusBadge(transaction.status).label }}
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Cashier</label>
                                    <div class="flex items-center gap-2 mt-1">
                                        <UserIcon class="h-4 w-4 text-gray-400" />
                                        <span class="font-medium">{{ transaction.user.name }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Date & Time</label>
                                    <div class="flex items-center gap-2 mt-1">
                                        <CalendarIcon class="h-4 w-4 text-gray-400" />
                                        <span class="font-medium">{{ formatDate(transaction.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="transaction.notes" class="pt-4 border-t">
                                <label class="text-sm font-medium text-gray-500">Notes</label>
                                <p class="mt-1 text-gray-700">{{ transaction.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Items -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Items ({{ transaction.items.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-2 font-medium text-gray-500">Product</th>
                                                <th class="text-center py-2 font-medium text-gray-500">Qty</th>
                                                <th class="text-right py-2 font-medium text-gray-500">Unit Price</th>
                                                <th class="text-right py-2 font-medium text-gray-500">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="item in transaction.items"
                                                :key="item.id"
                                                class="border-b last:border-b-0"
                                            >
                                                <td class="py-3">
                                                    <div>
                                                        <p class="font-medium">{{ item.product_name }}</p>
                                                        <p class="text-sm text-gray-500">{{ item.product.category.name }}</p>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <span class="font-medium">{{ item.quantity }} pcs</span>
                                                </td>
                                                <td class="py-3 text-right">
                                                    <span class="font-medium">{{ formatCurrency(item.unit_price) }}</span>
                                                </td>
                                                <td class="py-3 text-right">
                                                    <span class="font-semibold">{{ formatCurrency(item.subtotal) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Totals -->
                                <div class="border-t pt-4 space-y-2">
                                    <div class="flex justify-between text-lg">
                                        <span class="font-medium">Subtotal:</span>
                                        <span class="font-semibold">
                                            {{ formatCurrency(
                                                transaction.items.reduce((sum, item) => sum + parseFloat(item.subtotal), 0)
                                            ) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-xl font-bold text-blue-600">
                                        <span>Total:</span>
                                        <span>{{ formatCurrency(transaction.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Payment Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <component :is="getPaymentMethodIcon(transaction.payment_method)" class="h-5 w-5" />
                                Payment Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Payment Method</p>
                                <p class="text-lg font-semibold">{{ getPaymentMethodLabel(transaction.payment_method) }}</p>
                            </div>

                            <div class="space-y-3 pt-3 border-t">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Total Amount</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Payment Amount</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.payment_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span class="text-sm font-medium">Change</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.change_amount) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Transaction Stats</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Items</span>
                                <span class="font-semibold">{{ transaction.items.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Quantity</span>
                                <span class="font-semibold">
                                    {{ transaction.items.reduce((sum, item) => sum + item.quantity, 0) }} pcs
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Average Price</span>
                                <span class="font-semibold">
                                    {{ formatCurrency(
                                        transaction.items.length > 0
                                            ? transaction.items.reduce((sum, item) => sum + parseFloat(item.unit_price), 0) / transaction.items.length
                                            : 0
                                    ) }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                @click="printReceipt"
                            >
                                <PrinterIcon class="h-4 w-4" />
                                Print Receipt
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                as-child
                            >
                                <Link href="/transactions/daily-report">
                                    <FileTextIcon class="h-4 w-4" />
                                    Daily Report
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
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

    .sidebar {
        page-break-inside: avoid;
    }
}
</style>
