<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { useTransactions, type Transaction } from '@/composables/useTransactions'
import {
    ArrowLeftIcon,
    PrinterIcon,
    UserIcon,
    CalendarIcon,
    DollarSignIcon,
    CreditCardIcon,
    PackageIcon,
    FileTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon
} from 'lucide-vue-next'
import AppPageHeader from '@/components/AppPageHeader.vue'

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
        'cash': 'Tunai',
        'debit': 'Kartu Debit',
        'credit': 'Kartu Kredit',
        'e-wallet': 'E-Wallet'
    }
    return labels[method] || method
}

const getStatusBadge = (status: string) => {
    const statuses: Record<string, {
        label: string;
        variant: 'default' | 'secondary' | 'destructive' | 'warning';
        icon: any
    }> = {
        'completed': { label: 'Selesai', variant: 'default', icon: CheckCircleIcon },
        'pending': { label: 'Menunggu', variant: 'warning', icon: ClockIcon },
        'cancelled': { label: 'Dibatalkan', variant: 'destructive', icon: XCircleIcon }
    }
    return statuses[status] || { label: status, variant: 'default' as const, icon: CheckCircleIcon }
}

const printReceipt = () => {
    window.print()
}
</script>

<template>
    <Head :title="`Transaksi - ${transaction.transaction_number}`" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader
                :title="`Detail Transaksi - ${transaction.transaction_number}`"
                :description="`Informasi lengkap transaksi: ${transaction.transaction_number}`"
            >
                <template #actions>
                    <Button variant="ghost" size="sm" @click="visitIndex()" class="gap-2">
                        <ArrowLeftIcon /> Kembali ke Transaksi
                    </Button>
                </template>
            </AppPageHeader>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Transaction Info -->
                <div class="lg:col-span-2">
                    <!-- Transaction Summary -->
                    <Card class="mb-6">
                        <CardHeader>
                            <CardTitle>Informasi Transaksi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Transaction Number -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Nomor Transaksi</Label>
                                <p class="text-lg font-mono font-semibold">{{ transaction.transaction_number }}</p>
                            </div>

                            <!-- Status -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <div>
                                    <Badge :variant="getStatusBadge(transaction.status).variant" class="gap-1">
                                        <component :is="getStatusBadge(transaction.status).icon" class="h-3 w-3" />
                                        {{ getStatusBadge(transaction.status).label }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Cashier and Date -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Kasir</Label>
                                    <div class="flex items-center gap-2">
                                        <UserIcon class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ transaction.user.name }}</span>
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Tanggal & Waktu</Label>
                                    <div class="flex items-center gap-2">
                                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-medium">{{ formatDate(transaction.created_at) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Metode Pembayaran</Label>
                                <div class="flex items-center gap-2">
                                    <component :is="getPaymentMethodIcon(transaction.payment_method)" class="h-4 w-4 text-muted-foreground" />
                                    <span class="font-medium">{{ getPaymentMethodLabel(transaction.payment_method) }}</span>
                                </div>
                            </div>

                            <!-- Total Amount -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Total</Label>
                                <p class="text-2xl font-mono font-bold text-primary">{{ formatCurrency(transaction.total_amount) }}</p>
                            </div>

                            <!-- Notes -->
                            <div v-if="transaction.notes" class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Catatan</Label>
                                <p class="text-foreground whitespace-pre-wrap">{{ transaction.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Items -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Item Transaksi ({{ transaction.items.length }})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-2 font-medium text-muted-foreground">Produk</th>
                                                <th class="text-center py-2 font-medium text-muted-foreground">Qty</th>
                                                <th class="text-right py-2 font-medium text-muted-foreground">Harga Satuan</th>
                                                <th class="text-right py-2 font-medium text-muted-foreground">Subtotal</th>
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
                                                        <p class="text-sm text-muted-foreground">{{ item.product.category.name }}</p>
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
                                    <div class="flex justify-between text-xl font-bold text-primary">
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
                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Aksi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                @click="printReceipt"
                            >
                                <PrinterIcon class="h-4 w-4" />
                                Cetak Struk
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full gap-2"
                                as-child
                            >
                                <Link href="/transactions/daily-report">
                                    <FileTextIcon class="h-4 w-4" />
                                    Laporan Harian
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Payment Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <component :is="getPaymentMethodIcon(transaction.payment_method)" class="h-5 w-5" />
                                Detail Pembayaran
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-sm font-medium text-muted-foreground">Metode Pembayaran</Label>
                                <p class="text-lg font-semibold">{{ getPaymentMethodLabel(transaction.payment_method) }}</p>
                            </div>

                            <div class="space-y-3 pt-3 border-t">
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Total Tagihan</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Jumlah Bayar</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.payment_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span class="text-sm font-medium">Kembalian</span>
                                    <span class="font-semibold">{{ formatCurrency(transaction.change_amount) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Transaction Stats -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Statistik Transaksi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Item</span>
                                <span class="font-semibold">{{ transaction.items.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Kuantitas</span>
                                <span class="font-semibold">
                                    {{ transaction.items.reduce((sum, item) => sum + item.quantity, 0) }} pcs
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Rata-rata Harga</span>
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

                    <!-- Timeline -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5" />
                                Timeline
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Dibuat</p>
                                <p class="text-sm">{{ formatDate(transaction.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Terakhir Diperbarui</p>
                                <p class="text-sm">{{ formatDate(transaction.updated_at) }}</p>
                            </div>
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
