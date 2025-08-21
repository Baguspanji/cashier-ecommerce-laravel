<script setup lang="ts">
import { reactive } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { useProducts, type Product, type ProductData } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import { ArrowLeftIcon } from 'lucide-vue-next'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select'

interface Props {
    product: Product
    categories: Category[]
}

const props = defineProps<Props>()

const { loading, errors, update, visitIndex } = useProducts()

// Form data
const form = reactive<ProductData>({
    name: props.product.name,
    barcode: props.product.barcode || '',
    description: props.product.description || '',
    category_id: props.product.category_id,
    price: parseFloat(props.product.price),
    price_purchase: parseFloat(String(props.product.price_purchase || '0')) || 0,
    current_stock: props.product.current_stock,
    minimum_stock: props.product.minimum_stock,
    is_active: props.product.is_active,
})

const handleSubmit = () => {
    update(props.product.id, form)
}

const handleCancel = () => {
    visitIndex()
}
</script>

<template>

    <Head :title="`Edit Produk - ${product.name}`" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Edit Produk" :description="`Edit informasi produk: ${product.name}`">
                <template #actions>
                    <Button variant="ghost" size="sm" @click="visitIndex()" class="gap-2">
                        <ArrowLeftIcon /> Kembali ke Produk
                    </Button>
                </template>
            </AppPageHeader>

            <!-- Form -->
            <form @submit.prevent="handleSubmit">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Produk</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Name -->
                                <div class="grid gap-2">
                                    <Label for="name">Nama Produk</Label>
                                    <Input id="name" v-model="form.name" placeholder="Masukkan nama produk"
                                        :disabled="loading" />
                                    <div v-if="errors.name" class="text-sm text-destructive">
                                        {{ errors.name }}
                                    </div>
                                </div>

                                <!-- Barcode -->
                                <div class="grid gap-2">
                                    <Label for="barcode">Barcode (Opsional)</Label>
                                    <Input id="barcode" v-model="form.barcode" placeholder="Scan atau masukkan barcode"
                                        :disabled="loading" />
                                    <div v-if="errors.barcode" class="text-sm text-destructive">
                                        {{ errors.barcode }}
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Kosongkan untuk menggunakan barcode otomatis
                                    </p>
                                </div>

                                <!-- Description -->
                                <div class="grid gap-2">
                                    <Label for="description">Deskripsi (Opsional)</Label>
                                    <Input id="description" v-model="form.description"
                                        placeholder="Masukkan deskripsi produk" :disabled="loading" />
                                    <div v-if="errors.description" class="text-sm text-destructive">
                                        {{ errors.description }}
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="grid gap-2">
                                    <Label for="category">Kategori</Label>
                                    <Select v-model="form.category_id" class="h-10">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih Kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Kategori Produk</SelectLabel>
                                                <SelectItem v-for="category in props.categories" :key="category.id"
                                                    :value="category.id">
                                                    {{ category.name }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <div v-if="errors.category_id" class="text-sm text-destructive">
                                        {{ errors.category_id }}
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="price_purchase">Harga Beli</Label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-[40%] h-4 w-4 -translate-y-1/2 text-muted-foreground">Rp</span>
                                            <Input id="price_purchase" v-model.number="form.price_purchase"
                                                type="number" min="0" placeholder="0" :disabled="loading"
                                                class="pl-10" />
                                        </div>
                                        <div v-if="errors.price_purchase" class="text-sm text-destructive">
                                            {{ errors.price_purchase }}
                                        </div>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="price">Harga Jual</Label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-[40%] h-4 w-4 -translate-y-1/2 text-muted-foreground">Rp</span>
                                            <Input id="price" v-model.number="form.price" type="number" min="0"
                                                placeholder="0" :disabled="loading" class="pl-10" />
                                        </div>
                                        <div v-if="errors.price" class="text-sm text-destructive">
                                            {{ errors.price }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Information -->
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="current_stock">Stok Saat Ini</Label>
                                        <Input id="current_stock" v-model.number="form.current_stock" type="number"
                                            min="0" placeholder="0" :disabled="loading" />
                                        <div v-if="errors.current_stock" class="text-sm text-destructive">
                                            {{ errors.current_stock }}
                                        </div>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="minimum_stock">Stok Minimum</Label>
                                        <Input id="minimum_stock" v-model.number="form.minimum_stock" type="number"
                                            min="0" placeholder="0" :disabled="loading" />
                                        <div v-if="errors.minimum_stock" class="text-sm text-destructive">
                                            {{ errors.minimum_stock }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Status -->
                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" :disabled="loading" />
                                    <Label for="is_active">Produk aktif</Label>
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
                                <Button @click="handleSubmit" :disabled="loading" class="w-full">
                                    {{ loading ? 'Mengupdate...' : 'Update Produk' }}
                                </Button>
                                <Button variant="outline" @click="handleCancel" :disabled="loading" class="w-full">
                                    Batal
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- Image Upload (Future) -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Gambar Produk</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                    <p class="text-sm text-muted-foreground">
                                        Upload gambar akan tersedia di versi mendatang
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
