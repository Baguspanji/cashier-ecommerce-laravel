<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import BarcodeScanner from '@/components/BarcodeScanner.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { useTransactions, type CreateTransactionData } from '@/composables/useTransactions'
import { useOfflineSync } from '@/composables/useOfflineSync'
import { useNotifications } from '@/composables/useNotifications'
import { type Product } from '@/composables/useProducts'
import {
    ShoppingCartIcon,
    PlusIcon,
    MinusIcon,
    TrashIcon,
    CreditCardIcon,
    BanknoteIcon,
    SmartphoneIcon,
    SearchIcon,
    ScanIcon,
    WifiOffIcon,
    WifiIcon
} from 'lucide-vue-next'

interface Props {
    products: Product[]
}

interface CartItem {
    product: Product
    quantity: number
    subtotal: number
}

const props = defineProps<Props>()

const { loading, errors, store } = useTransactions()
const { isOnline, storeOfflineTransaction, isSyncing } = useOfflineSync()
const { addNotification } = useNotifications()

// State management
const search = ref('')
const cart = ref<CartItem[]>([])
const paymentMethod = ref<'cash' | 'debit' | 'credit' | 'e-wallet'>('cash')
const paymentAmount = ref(0)
const notes = ref('')
const showBarcodeScanner = ref(false)

// Computed values
const filteredProducts = computed(() => {
    if (!search.value) return props.products
    return props.products.filter(product =>
        product.name.toLowerCase().includes(search.value.toLowerCase()) ||
        product.barcode?.includes(search.value) ||
        (product.category?.name?.toLowerCase().includes(search.value.toLowerCase()) || false)
    )
})

const totalAmount = computed(() => {
    return cart.value.reduce((total, item) => total + item.subtotal, 0)
})

const changeAmount = computed(() => {
    return Math.max(0, paymentAmount.value - totalAmount.value)
})

const canCheckout = computed(() => {
    return cart.value.length > 0 && paymentAmount.value >= totalAmount.value
})

// Functions
const formatPrice = (price: string | number) => {
    const numPrice = typeof price === 'string' ? parseFloat(price) : price
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(numPrice)
}

const addToCart = (product: Product) => {
    if (product.current_stock <= 0) return

    const existingItem = cart.value.find(item => item.product.id === product.id)

    if (existingItem) {
        if (existingItem.quantity < product.current_stock) {
            existingItem.quantity++
            existingItem.subtotal = existingItem.quantity * parseFloat(product.price)
        }
    } else {
        cart.value.push({
            product,
            quantity: 1,
            subtotal: parseFloat(product.price)
        })
    }
}

const updateQuantity = (productId: number, quantity: number) => {
    const item = cart.value.find(item => item.product.id === productId)
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId)
        } else if (quantity <= item.product.current_stock) {
            item.quantity = quantity
            item.subtotal = quantity * parseFloat(item.product.price)
        }
    }
}

const removeFromCart = (productId: number) => {
    cart.value = cart.value.filter(item => item.product.id !== productId)
}

const clearCart = () => {
    cart.value = []
    paymentAmount.value = 0
    notes.value = ''
}

const checkout = async () => {
    const transactionData: CreateTransactionData = {
        items: cart.value.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity
        })),
        payment_method: paymentMethod.value,
        payment_amount: paymentAmount.value,
        notes: notes.value || undefined
    }

    if (isOnline.value) {
        // Online mode: use regular store function
        try {
            await store(transactionData)

            // Clear cart if successful
            if (!Object.keys(errors.value).length) {
                clearCart()
                addNotification({
                    type: 'success',
                    title: 'Transaksi Berhasil',
                    message: `Transaksi sebesar ${formatPrice(totalAmount.value)} berhasil diproses`
                })
            }
        } catch (error: unknown) {
            console.error('Online transaction error:', error)
            addNotification({
                type: 'error',
                title: 'Transaksi Gagal',
                message: 'Terjadi kesalahan saat memproses transaksi'
            })
        }
    } else {
        // Offline mode: store transaction locally
        try {
            const offlineTransaction = {
                customer_name: undefined, // Optional for POS
                total_amount: totalAmount.value,
                payment_method: paymentMethod.value as 'cash' | 'bank_transfer' | 'e_wallet',
                items: cart.value.map(item => ({
                    product_id: item.product.id,
                    quantity: item.quantity,
                    price: parseFloat(item.product.price)
                })),
                created_at: new Date().toISOString()
            }

            await storeOfflineTransaction(offlineTransaction)
            clearCart()

            addNotification({
                type: 'success',
                title: 'Transaksi Tersimpan Offline',
                message: `Transaksi sebesar ${formatPrice(totalAmount.value)} disimpan dan akan disinkronkan nanti`
            })
        } catch (error: unknown) {
            console.error('Failed to store offline transaction:', error)
            addNotification({
                type: 'error',
                title: 'Gagal Menyimpan Transaksi',
                message: 'Terjadi kesalahan saat menyimpan transaksi offline'
            })
        }
    }
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

const getPaymentMethodIcon = (method: string) => {
    switch (method) {
        case 'cash': return BanknoteIcon
        case 'debit':
        case 'credit': return CreditCardIcon
        case 'e-wallet': return SmartphoneIcon
        default: return BanknoteIcon
    }
}

const setPaymentAmount = (amount: number) => {
    paymentAmount.value = amount
}

const quickPaymentAmounts = [5000, 10000, 20000, 50000, 100000]

const handleBarcodeScanned = (barcode: string) => {
    showBarcodeScanner.value = false

    // Search for product by barcode
    const product = props.products.find(p => p.barcode === barcode)

    if (product) {
        if (product.current_stock > 0 && product.is_active) {
            addToCart(product)
            addNotification({
                type: 'success',
                title: 'Produk Ditambahkan',
                message: `${product.name} berhasil ditambahkan ke keranjang`
            })
        } else {
            addNotification({
                type: 'warning',
                title: 'Produk Tidak Tersedia',
                message: `${product.name} sedang tidak tersedia atau stok habis`
            })
        }
    } else {
        addNotification({
            type: 'error',
            title: 'Barcode Tidak Ditemukan',
            message: `Produk dengan barcode ${barcode} tidak ditemukan`
        })
        // Set search value to barcode for manual search
        search.value = barcode
    }
}

const openBarcodeScanner = () => {
    showBarcodeScanner.value = true
}

const closeBarcodeScanner = () => {
    showBarcodeScanner.value = false
}

</script>

<template>

    <Head title="Point of Sale" />

    <AppLayout>
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Products Section -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-end justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold leading-tight">POS</h1>
                            <div class="flex items-center gap-1 text-sm">
                                <div :class="[
                                    'w-2 h-2 rounded-full',
                                    isOnline ? 'bg-green-500' : 'bg-red-500'
                                ]"></div>
                                <span :class="[
                                    'font-medium',
                                    isOnline ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                ]">
                                    {{ isOnline ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-muted-foreground">
                            Kelola transaksi penjualan dengan mudah{{ !isOnline ? ' (Mode Offline)' : '' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button @click="openBarcodeScanner" variant="outline" size="sm">
                            <ScanIcon class="mr-2 h-4 w-4" />
                            Scan Barcode
                        </Button>
                        <div class="relative">
                            <SearchIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari produk/barcode..." class="pl-10 w-64" />
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-3">
                    <Card v-for="product in filteredProducts" :key="product.id"
                        class="hover:shadow-md transition-shadow">
                        <CardContent>
                            <div class="space-y-2">
                                <div>
                                    <h4 class="font-medium text-sm leading-tight">{{ product.name }}</h4>
                                    <p class="text-xs text-muted-foreground">{{ product.category?.name || 'Tanpa Kategori' }}</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold">{{ formatPrice(product.price) }}</div>
                                    <div class="text-xs text-muted-foreground">Stok: {{ product.current_stock }}</div>
                                </div>
                                <Button size="sm" class="w-full h-7 text-xs" :disabled="product.current_stock === 0"
                                    @click="addToCart(product)">
                                    <PlusIcon class="mr-1 h-3 w-3" />
                                    {{ product.current_stock === 0 ? 'Habis' : 'Tambah' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div v-if="!filteredProducts.length" class="text-center py-8">
                    <p class="text-muted-foreground">Tidak ada produk ditemukan</p>
                </div>
            </div>

            <!-- Cart & Checkout Section -->
            <div class="space-y-6">
                <!-- Cart -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <ShoppingCartIcon class="mr-2 h-5 w-5" />
                            Keranjang ({{ cart.length }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="cart.length === 0" class="text-center py-4">
                            <p class="text-muted-foreground">Keranjang kosong</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="item in cart" :key="item.product.id"
                                class="flex items-center justify-between p-3 border rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm">{{ item.product.name }}</h4>
                                    <p class="text-xs text-muted-foreground">{{ formatPrice(item.product.price) }}</p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Button variant="outline" size="icon" class="h-6 w-6"
                                        @click="updateQuantity(item.product.id, item.quantity - 1)">
                                        <MinusIcon class="h-3 w-3" />
                                    </Button>

                                    <span class="w-8 text-center text-sm">{{ item.quantity }}</span>

                                    <Button variant="outline" size="icon" class="h-6 w-6"
                                        @click="updateQuantity(item.product.id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.product.current_stock">
                                        <PlusIcon class="h-3 w-3" />
                                    </Button>

                                    <Button variant="ghost" size="icon" class="h-6 w-6 text-destructive"
                                        @click="removeFromCart(item.product.id)">
                                        <TrashIcon class="h-3 w-3" />
                                    </Button>
                                </div>

                                <div class="text-right ml-2">
                                    <div class="font-medium text-sm">{{ formatPrice(item.subtotal) }}</div>
                                </div>
                            </div>

                            <div class="border-t pt-3">
                                <div class="flex justify-between items-center font-bold text-lg">
                                    <span>Total:</span>
                                    <span>{{ formatPrice(totalAmount) }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment -->
                <Card v-if="cart.length > 0">
                    <CardHeader>
                        <CardTitle>Pembayaran</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Payment Method -->
                        <div class="grid gap-2">
                            <Label>Metode Pembayaran</Label>
                            <div class="grid grid-cols-2 gap-2">
                                <Button v-for="method in ['cash', 'debit', 'credit', 'e-wallet']" :key="method"
                                    :variant="paymentMethod === method ? 'default' : 'outline'" size="sm"
                                    @click="paymentMethod = method as any"
                                    class="flex items-center justify-center space-x-2">
                                    <component :is="getPaymentMethodIcon(method)" class="h-4 w-4" />
                                    <span>{{ getPaymentMethodLabel(method) }}</span>
                                </Button>
                            </div>
                        </div>

                        <!-- Payment Amount -->
                        <div class="grid gap-2">
                            <Label for="payment">Jumlah Bayar</Label>
                            <Input id="payment" v-model.number="paymentAmount" type="number" :min="totalAmount"
                                step="1000" placeholder="0" />

                            <!-- Quick Payment Buttons -->
                            <div class="grid grid-cols-3 gap-2 mt-2">
                                <Button
                                    v-for="amount in quickPaymentAmounts"
                                    :key="amount"
                                    variant="outline"
                                    size="sm"
                                    @click="setPaymentAmount(amount)"
                                    :disabled="amount < totalAmount"
                                    class="text-xs"
                                >
                                    {{ formatPrice(amount) }}
                                </Button>
                            </div>

                            <!-- Exact Amount Button -->
                            <Button
                                variant="outline"
                                size="sm"
                                @click="setPaymentAmount(totalAmount)"
                                class="mt-1"
                            >
                                Uang Pas ({{ formatPrice(totalAmount) }})
                            </Button>

                            <div v-if="errors.payment_amount" class="text-sm text-destructive">
                                {{ errors.payment_amount }}
                            </div>
                        </div>

                        <!-- Change -->
                        <div v-if="paymentAmount > 0" class="grid gap-2">
                            <Label>Kembalian</Label>
                            <div class="text-2xl font-bold">{{ formatPrice(changeAmount) }}</div>
                        </div>

                        <!-- Notes -->
                        <div class="grid gap-2">
                            <Label for="notes">Catatan (Opsional)</Label>
                            <Input id="notes" v-model="notes" placeholder="Catatan transaksi..." />
                        </div>

                        <!-- Actions -->
                        <div class="space-y-2 pt-4">
                            <div v-if="!isOnline" class="flex items-center gap-2 text-sm text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 p-2 rounded-md">
                                <WifiOffIcon class="h-4 w-4" />
                                <span>Mode Offline - Transaksi akan disinkronkan nanti</span>
                            </div>

                            <Button @click="checkout" :disabled="!canCheckout || loading || isSyncing" class="w-full" size="lg">
                                <WifiOffIcon v-if="!isOnline" class="h-4 w-4 mr-2" />
                                <WifiIcon v-else class="h-4 w-4 mr-2" />
                                {{ loading || isSyncing ? 'Memproses...' : isOnline ? 'Bayar' : 'Bayar (Offline)' }}
                            </Button>

                            <Button variant="outline" @click="clearCart" class="w-full" :disabled="loading">
                                Kosongkan Keranjang
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Barcode Scanner Modal -->
        <BarcodeScanner
            :is-open="showBarcodeScanner"
            title="Scan Product Barcode"
            @scanned="handleBarcodeScanned"
            @close="closeBarcodeScanner"
        />
    </AppLayout>
</template>
