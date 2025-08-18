<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
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

</script>

<template>

    <Head title="Kategori" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Kategori" description="Kelola kategori produk untuk organisasi yang lebih baik">
                <template #actions>
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
                                    <Input id="name" v-model="createForm.name" placeholder="Masukkan nama kategori"
                                        :disabled="loading" />
                                    <div v-if="errors.name" class="text-sm text-destructive">
                                        {{ errors.name }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="description">Deskripsi (Opsional)</Label>
                                    <Input id="description" v-model="createForm.description"
                                        placeholder="Masukkan deskripsi kategori" :disabled="loading" />
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
                </template>
            </AppPageHeader>

            <!-- Filters -->
            <Card>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <!-- Search -->
                        <div class="grid gap-2">
                            <Label for="search">Cari Kategori</Label>
                            <div class="relative">
                                <SearchIcon class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Nama atau deskripsi..." class="pl-10" />
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2 md:col-start-4">
                            <Label>Total</Label>
                            <div class="text-2xl font-bold">
                                {{ props.categories?.meta?.total || props.categories.data.length }} kategori
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Categories Grid -->
            <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <Card v-for="category in props.categories.data" :key="category.id"
                    class="hover:shadow-md transition-shadow">
                    <CardContent>
                        <div class="space-y-2">
                            <!-- Header with actions -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-sm leading-tight truncate">{{ category.name }}</h4>
                                    <p class="text-xs text-muted-foreground">{{ category.products_count || 0 }} produk</p>
                                </div>
                                <div class="flex space-x-1 ml-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(category)" class="h-6 w-6">
                                        <PencilIcon class="h-3 w-3" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="handleDelete(category)" class="h-6 w-6"
                                        :disabled="(category.products_count || 0) > 0">
                                        <TrashIcon class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="category.description" class="text-xs text-muted-foreground">
                                {{ category.description }}
                            </div>
                        </div>
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
                            <Input id="edit-name" v-model="editForm.name" placeholder="Masukkan nama kategori"
                                :disabled="loading" />
                            <div v-if="errors.name" class="text-sm text-destructive">
                                {{ errors.name }}
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label for="edit-description">Deskripsi (Opsional)</Label>
                            <Input id="edit-description" v-model="editForm.description"
                                placeholder="Masukkan deskripsi kategori" :disabled="loading" />
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
