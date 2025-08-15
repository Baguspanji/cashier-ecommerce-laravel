<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { useProducts, type ProductData } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import { ArrowLeftIcon } from 'lucide-vue-next'

interface Props {
    categories: Category[]
}

const props = defineProps<Props>()

const { loading, errors, store, visitIndex } = useProducts()

// Form data
const form = ref<ProductData>({
    name: '',
    description: '',
    category_id: 0,
    price: 0,
    current_stock: 0,
    minimum_stock: 0,
    is_active: true,
})

// Functions
const handleSubmit = () => {
    store(form.value)
}

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Produk', href: route('products.index') },
    { title: 'Tambah Produk', href: route('products.create') },
]
</script>

<template>
    <Head title="Tambah Produk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center space-x-4">
                <Button variant="ghost" size="icon" @click="visitIndex">
                    <ArrowLeftIcon class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tambah Produk</h1>
                    <p class="text-muted-foreground">
                        Tambahkan produk baru ke inventori Anda
                    </p>
                </div>
            </div>

            <!-- Form -->
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
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Masukkan nama produk"
                                    :disabled="loading"
                                />
                                <div v-if="errors.name" class="text-sm text-destructive">
                                    {{ errors.name }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="grid gap-2">
                                <Label for="description">Deskripsi (Opsional)</Label>
                                <Input
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Masukkan deskripsi produk"
                                    :disabled="loading"
                                />
                                <div v-if="errors.description" class="text-sm text-destructive">
                                    {{ errors.description }}
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="grid gap-2">
                                <Label for="category">Kategori</Label>
                                <select
                                    id="category"
                                    v-model="form.category_id"
                                    :disabled="loading"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="0">Pilih kategori</option>
                                    <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="errors.category_id" class="text-sm text-destructive">
                                    {{ errors.category_id }}
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="grid gap-2">
                                <Label for="price">Harga</Label>
                                <Input
                                    id="price"
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    step="500"
                                    placeholder="0"
                                    :disabled="loading"
                                />
                                <div v-if="errors.price" class="text-sm text-destructive">
                                    {{ errors.price }}
                                </div>
                            </div>

                            <!-- Stock Information -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="current_stock">Stok Awal</Label>
                                    <Input
                                        id="current_stock"
                                        v-model.number="form.current_stock"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        :disabled="loading"
                                    />
                                    <div v-if="errors.current_stock" class="text-sm text-destructive">
                                        {{ errors.current_stock }}
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label for="minimum_stock">Stok Minimum</Label>
                                    <Input
                                        id="minimum_stock"
                                        v-model.number="form.minimum_stock"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        :disabled="loading"
                                    />
                                    <div v-if="errors.minimum_stock" class="text-sm text-destructive">
                                        {{ errors.minimum_stock }}
                                    </div>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                    :disabled="loading"
                                />
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
                                {{ loading ? 'Menyimpan...' : 'Simpan Produk' }}
                            </Button>
                            <Button variant="outline" @click="visitIndex" :disabled="loading" class="w-full">
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
        </div>
    </AppLayout>
</template>
