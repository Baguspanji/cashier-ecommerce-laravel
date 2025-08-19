<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { useProducts, type Product } from '@/composables/useProducts'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    PackageIcon,
    TagIcon,
    CalendarIcon
} from 'lucide-vue-next'
import AppPageHeader from '@/components/AppPageHeader.vue'

interface Props {
    product: Product
}

const props = defineProps<Props>()

const { loading, destroy, toggleStatus, visitEdit, visitIndex } = useProducts()

// Delete dialog state
const isDeleteDialogOpen = ref(false)
const productToDelete = ref<Product | null>(null)

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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getStockStatusColor = (stock: number, minStock: number) => {
    if (stock <= 0) return 'destructive'
    if (stock <= minStock) return 'warning'
    return 'default'
}

const getStockStatusText = (stock: number, minStock: number) => {
    if (stock <= 0) return 'Stok Habis'
    if (stock <= minStock) return 'Stok Rendah'
    return 'Stok Tersedia'
}

// Functions
const handleDelete = () => {
    isDeleteDialogOpen.value = true
}

const confirmDelete = () => {
    destroy(props.product.id)
    isDeleteDialogOpen.value = false
}

const cancelDelete = () => {
    isDeleteDialogOpen.value = false
}
</script>

<template>

    <Head :title="`Produk - ${product.name}`" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader :title="`Detail Produk - ${product.name}`"
                :description="`Informasi lengkap produk: ${product.name}`">
                <template #actions>
                    <Button variant="ghost" size="sm" @click="visitIndex()" class="gap-2">
                        <ArrowLeftIcon /> Kembali ke Produk
                    </Button>
                </template>
            </AppPageHeader>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Product Info -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Produk</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Name -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Nama Produk</Label>
                                <p class="text-lg font-semibold">{{ product.name }}</p>
                            </div>

                            <!-- Description -->
                            <div v-if="product.description" class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Deskripsi</Label>
                                <p class="text-foreground whitespace-pre-wrap">{{ product.description }}</p>
                            </div>

                            <!-- Category -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Kategori</Label>
                                <div class="flex items-center gap-2">
                                    <TagIcon class="h-4 w-4 text-muted-foreground" />
                                    <span class="font-medium">{{ product.category?.name }}</span>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Harga</Label>
                                <p class="text-lg font-mono">{{ formatCurrency(parseFloat(product.price)) }}</p>
                            </div>

                            <!-- Stock Information -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Stok Saat Ini</Label>
                                    <p class="text-lg font-semibold">{{ product.current_stock }} pcs</p>
                                </div>
                                <div class="grid gap-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Stok Minimum</Label>
                                    <p class="text-lg font-semibold">{{ product.minimum_stock }} pcs</p>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="grid gap-2">
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <div>
                                    <Badge :variant="product.is_active ? 'default' : 'destructive'" class="gap-1">
                                        <CheckCircleIcon v-if="product.is_active" class="h-3 w-3" />
                                        <XCircleIcon v-else class="h-3 w-3" />
                                        {{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </Badge>
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
                            <Button variant="outline" class="w-full gap-2" @click="visitEdit(product.id)"
                                :disabled="loading">
                                <PencilIcon class="h-4 w-4" />
                                Edit Produk
                            </Button>
                            <Button variant="outline" class="w-full gap-2" @click="toggleStatus(product.id)"
                                :disabled="loading">
                                <component :is="product.is_active ? XCircleIcon : CheckCircleIcon" class="h-4 w-4" />
                                {{ product.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </Button>
                            <Button variant="destructive" class="w-full gap-2" @click="handleDelete()"
                                :disabled="loading">
                                <TrashIcon class="h-4 w-4" />
                                Hapus Produk
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Stock Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status Stok</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="text-center">
                                <Badge :variant="getStockStatusColor(product.current_stock, product.minimum_stock)"
                                    class="text-sm px-3 py-1">
                                    {{ getStockStatusText(product.current_stock, product.minimum_stock) }}
                                </Badge>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Stok Saat Ini</span>
                                    <span class="font-semibold">{{ product.current_stock }} pcs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Stok Minimum</span>
                                    <span class="font-semibold">{{ product.minimum_stock }} pcs</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-muted-foreground">Satuan</span>
                                    <span class="font-semibold">pcs</span>
                                </div>
                            </div>

                            <!-- Stock Level Progress -->
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Level Stok</span>
                                    <span>{{ Math.round((product.current_stock / (product.minimum_stock * 3)) * 100)
                                    }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all" :class="{
                                        'bg-red-500': product.current_stock <= 0,
                                        'bg-yellow-500': product.current_stock > 0 && product.current_stock <= product.minimum_stock,
                                        'bg-green-500': product.current_stock > product.minimum_stock
                                    }"
                                        :style="{ width: Math.min(100, Math.max(5, (product.current_stock / (product.minimum_stock * 3)) * 100)) + '%' }">
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Additional Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Riwayat & Laporan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <Button variant="outline" class="w-full gap-2" as-child>
                                <Link href="/stock/overview">
                                <PackageIcon class="h-4 w-4" />
                                Lihat Riwayat Stok
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Timestamps -->
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
                                <p class="text-sm">{{ formatDate(product.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Terakhir Diperbarui</p>
                                <p class="text-sm">{{ formatDate(product.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Image Upload (Future) -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Gambar Produk</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                <p class="text-sm text-muted-foreground">
                                    Upload gambar akan tersedia di versi mendatang
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Hapus Produk</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus produk <strong>{{ productToDelete?.name }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                    <Button type="button" variant="outline" @click="cancelDelete">
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" @click="confirmDelete">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
