<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { useCategories, type Category, type CategoryData } from '@/composables/useCategories'
import { PlusIcon, SearchIcon, PencilIcon, TrashIcon } from 'lucide-vue-next'

interface Props {
    categories: {
        data: Category[]
        links: any[]
        meta: any
    }
    filters: {
        search?: string
    }
}

const props = defineProps<Props>()

const { loading, errors, store, update, destroy, visitIndex } = useCategories()

// State management
const search = ref(props.filters.search || '')
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const editingCategory = ref<Category | null>(null)

// Form data
const createForm = ref<CategoryData>({
    name: '',
    description: '',
})

const editForm = ref<CategoryData>({
    name: '',
    description: '',
})

// Watch search input for live filtering
watch(search, (value) => {
    visitIndex({ search: value })
})

// Functions
const openCreateDialog = () => {
    createForm.value = { name: '', description: '' }
    showCreateDialog.value = true
}

const openEditDialog = (category: Category) => {
    editingCategory.value = category
    editForm.value = {
        name: category.name,
        description: category.description || '',
    }
    showEditDialog.value = true
}

const handleCreate = () => {
    store(createForm.value)
    if (!Object.keys(errors.value).length) {
        showCreateDialog.value = false
    }
}

const handleUpdate = () => {
    if (editingCategory.value) {
        update(editingCategory.value.id, editForm.value)
        if (!Object.keys(errors.value).length) {
            showEditDialog.value = false
        }
    }
}

const handleDelete = (category: Category) => {
    destroy(category.id)
}

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Kategori', href: route('categories.index') },
]
</script>

<template>
    <Head title="Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Kategori</h1>
                    <p class="text-muted-foreground">
                        Kelola kategori produk untuk organisasi yang lebih baik
                    </p>
                </div>
                <Dialog v-model:open="showCreateDialog">
                    <DialogTrigger as-child>
                        <Button @click="openCreateDialog">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Tambah Kategori
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Tambah Kategori Baru</DialogTitle>
                            <DialogDescription>
                                Buat kategori baru untuk mengorganisir produk Anda
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-4 py-4">
                            <div class="grid gap-2">
                                <Label for="name">Nama Kategori</Label>
                                <Input
                                    id="name"
                                    v-model="createForm.name"
                                    placeholder="Masukkan nama kategori"
                                    :disabled="loading"
                                />
                                <div v-if="errors.name" class="text-sm text-destructive">
                                    {{ errors.name }}
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="description">Deskripsi (Opsional)</Label>
                                <Input
                                    id="description"
                                    v-model="createForm.description"
                                    placeholder="Masukkan deskripsi kategori"
                                    :disabled="loading"
                                />
                                <div v-if="errors.description" class="text-sm text-destructive">
                                    {{ errors.description }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" @click="handleCreate" :disabled="loading">
                                {{ loading ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Search -->
            <Card>
                <CardContent class="pt-6">
                    <div class="relative">
                        <SearchIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Cari kategori..."
                            class="pl-10"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Categories Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="category in props.categories.data" :key="category.id" class="hover:shadow-md transition-shadow">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-lg">{{ category.name }}</CardTitle>
                                <span class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                    {{ category.products_count || 0 }} produk
                                </span>
                            </div>
                            <div class="flex space-x-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="openEditDialog(category)"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    @click="handleDelete(category)"
                                    :disabled="(category.products_count || 0) > 0"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent v-if="category.description">
                        <p class="text-sm text-muted-foreground">{{ category.description }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Edit Dialog -->
            <Dialog v-model:open="showEditDialog">
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Edit Kategori</DialogTitle>
                        <DialogDescription>
                            Perbarui informasi kategori
                        </DialogDescription>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="edit-name">Nama Kategori</Label>
                            <Input
                                id="edit-name"
                                v-model="editForm.name"
                                placeholder="Masukkan nama kategori"
                                :disabled="loading"
                            />
                            <div v-if="errors.name" class="text-sm text-destructive">
                                {{ errors.name }}
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-description">Deskripsi (Opsional)</Label>
                            <Input
                                id="edit-description"
                                v-model="editForm.description"
                                placeholder="Masukkan deskripsi kategori"
                                :disabled="loading"
                            />
                            <div v-if="errors.description" class="text-sm text-destructive">
                                {{ errors.description }}
                            </div>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="submit" @click="handleUpdate" :disabled="loading">
                            {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Empty State -->
            <Card v-if="!props.categories.data.length" class="p-8 text-center">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Belum ada kategori</h3>
                    <p class="text-muted-foreground">
                        Mulai dengan membuat kategori pertama Anda
                    </p>
                    <Button @click="openCreateDialog" class="mt-4">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Tambah Kategori Pertama
                    </Button>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
