<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import AppPageHeader from '@/components/AppPageHeader.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { useUsers, type UserData } from '@/composables/useUsers'
import {
    PlusIcon,
    SearchIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    UserIcon
} from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
    role: string
    created_at: string
}

interface Role {
    value: string
    label: string
}

interface Props {
    users: User[]
    roles: Role[]
    filters?: {
        search?: string
    }
}

const props = defineProps<Props>()

const { loading, errors, store, update, destroy } = useUsers()

// State management
const search = ref(props.filters?.search || '')
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showViewDialog = ref(false)
const editingUser = ref<User | null>(null)
const viewingUser = ref<User | null>(null)

// Delete dialog state
const isDeleteDialogOpen = ref(false)
const userToDelete = ref<User | null>(null)

// Form data
const createForm = ref<UserData>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'cashier',
})

const editForm = ref<UserData>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'cashier',
})

// Watch search input for live filtering
watch(search, () => {
    // You can implement live search here if needed
    // visitIndex({ search: search.value })
})

// Functions
const openCreateDialog = () => {
    createForm.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'cashier',
    }
    showCreateDialog.value = true
}

const openEditDialog = (user: User) => {
    editingUser.value = user
    editForm.value = {
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role: user.role,
    }
    showEditDialog.value = true
}

const openViewDialog = (user: User) => {
    viewingUser.value = user
    showViewDialog.value = true
}

const handleCreate = () => {
    store(createForm.value)
    if (!Object.keys(errors.value).length) {
        showCreateDialog.value = false
    }
}

const handleUpdate = () => {
    if (editingUser.value) {
        update(editingUser.value.id, editForm.value)
        if (!Object.keys(errors.value).length) {
            showEditDialog.value = false
        }
    }
}

const confirmDelete = (user: User) => {
    userToDelete.value = user
    isDeleteDialogOpen.value = true
}

const deleteUser = () => {
    if (userToDelete.value) {
        destroy(userToDelete.value.id)
        isDeleteDialogOpen.value = false
        userToDelete.value = null
    }
}

const cancelDelete = () => {
    isDeleteDialogOpen.value = false
    userToDelete.value = null
}

const getRoleLabel = (role: string): string => {
    const roleMap = {
        'admin': 'Administrator',
        'manager': 'Manager',
        'cashier': 'Kasir'
    } as const
    return roleMap[role as keyof typeof roleMap] || role
}

const getRoleBadgeVariant = (role: string) => {
    const variantMap = {
        'admin': 'destructive',
        'manager': 'default',
        'cashier': 'secondary'
    } as const
    return variantMap[role as keyof typeof variantMap] || 'default'
}

const formatDate = (dateString: string): string => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatTime = (dateString: string): string => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head title="Pengguna" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <AppPageHeader title="Pengguna" description="Kelola pengguna dan hak akses sistem">
                <template #actions>
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger as-child>
                            <Button @click="openCreateDialog">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Tambah Pengguna
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px]">
                            <DialogHeader>
                                <DialogTitle>Tambah Pengguna Baru</DialogTitle>
                                <DialogDescription>
                                    Buat akun pengguna baru untuk sistem
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid gap-2">
                                    <Label for="name">Nama Lengkap</Label>
                                    <Input id="name" v-model="createForm.name" placeholder="Masukkan nama lengkap"
                                        :disabled="loading" />
                                    <div v-if="errors.name" class="text-sm text-destructive">
                                        {{ errors.name }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="email">Email</Label>
                                    <Input id="email" v-model="createForm.email" type="email" placeholder="Masukkan email"
                                        :disabled="loading" />
                                    <div v-if="errors.email" class="text-sm text-destructive">
                                        {{ errors.email }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="password">Password</Label>
                                    <Input id="password" v-model="createForm.password" type="password"
                                        placeholder="Masukkan password" :disabled="loading" />
                                    <div v-if="errors.password" class="text-sm text-destructive">
                                        {{ errors.password }}
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="password_confirmation">Konfirmasi Password</Label>
                                    <Input id="password_confirmation" v-model="createForm.password_confirmation"
                                        type="password" placeholder="Konfirmasi password" :disabled="loading" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="role">Peran</Label>
                                    <Select v-model="createForm.role" :disabled="loading">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih peran" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="role in props.roles" :key="role.value" :value="role.value">
                                                {{ role.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="errors.role" class="text-sm text-destructive">
                                        {{ errors.role }}
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
                            <Label for="search">Cari Pengguna</Label>
                            <div class="relative">
                                <SearchIcon
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="search" v-model="search" placeholder="Nama atau email..."
                                    class="pl-10" />
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="grid gap-2 md:col-start-4">
                            <Label>Total</Label>
                            <div class="text-2xl font-bold">
                                {{ props.users.length }} pengguna
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Nama</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Email</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Peran</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Tanggal Dibuat</th>
                                    <th class="text-left py-3 px-4 font-medium text-muted-foreground">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in props.users" :key="user.id"
                                    class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="text-sm font-medium">
                                            {{ user.name }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm text-muted-foreground">
                                            {{ user.email }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <Badge :variant="getRoleBadgeVariant(user.role)">
                                            {{ getRoleLabel(user.role) }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ formatDate(user.created_at) }}</div>
                                            <div class="text-muted-foreground">{{ formatTime(user.created_at) }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <Button variant="ghost" size="sm" @click="openViewDialog(user)">
                                                <EyeIcon class="h-4 w-4 mr-1" />
                                                Lihat
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="openEditDialog(user)">
                                                <PencilIcon class="h-4 w-4 mr-1" />
                                                Edit
                                            </Button>
                                            <Button variant="ghost" size="sm" @click="confirmDelete(user)"
                                                :data-testid="`delete-user-${user.id}`">
                                                <TrashIcon class="h-4 w-4 mr-1" />
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="!props.users.length" class="text-center py-12">
                            <UserIcon class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 class="text-lg font-semibold mb-2">Belum ada pengguna</h3>
                            <p class="text-muted-foreground mb-4">Mulai dengan membuat pengguna pertama Anda.</p>
                            <Button @click="openCreateDialog">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Tambah Pengguna Pertama
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- View Dialog -->
        <Dialog v-model:open="showViewDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Detail Pengguna</DialogTitle>
                    <DialogDescription>
                        Informasi lengkap pengguna
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4" v-if="viewingUser">
                    <div class="grid gap-2">
                        <Label>Nama Lengkap</Label>
                        <div class="px-3 py-2 text-sm bg-muted rounded-md">
                            {{ viewingUser.name }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Email</Label>
                        <div class="px-3 py-2 text-sm bg-muted rounded-md">
                            {{ viewingUser.email }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Peran</Label>
                        <div class="px-3 py-2 text-sm bg-muted rounded-md">
                            <Badge :variant="getRoleBadgeVariant(viewingUser.role)">
                                {{ getRoleLabel(viewingUser.role) }}
                            </Badge>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Tanggal Dibuat</Label>
                        <div class="px-3 py-2 text-sm bg-muted rounded-md">
                            <div class="font-medium">{{ formatDate(viewingUser.created_at) }}</div>
                            <div class="text-muted-foreground text-xs">{{ formatTime(viewingUser.created_at) }}</div>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>ID Pengguna</Label>
                        <div class="px-3 py-2 text-sm bg-muted rounded-md font-mono">
                            #{{ viewingUser.id }}
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showViewDialog = false">
                        Tutup
                    </Button>
                    <Button @click="() => { showViewDialog = false; openEditDialog(viewingUser!) }">
                        Edit Pengguna
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Edit Pengguna</DialogTitle>
                    <DialogDescription>
                        Perbarui informasi pengguna
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="edit-name">Nama Lengkap</Label>
                        <Input id="edit-name" v-model="editForm.name" placeholder="Masukkan nama lengkap"
                            :disabled="loading" />
                        <div v-if="errors.name" class="text-sm text-destructive">
                            {{ errors.name }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-email">Email</Label>
                        <Input id="edit-email" v-model="editForm.email" type="email" placeholder="Masukkan email"
                            :disabled="loading" />
                        <div v-if="errors.email" class="text-sm text-destructive">
                            {{ errors.email }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-password">Password (Kosongkan jika tidak ingin mengubah)</Label>
                        <Input id="edit-password" v-model="editForm.password" type="password"
                            placeholder="Masukkan password baru" :disabled="loading" />
                        <div v-if="errors.password" class="text-sm text-destructive">
                            {{ errors.password }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-password_confirmation">Konfirmasi Password</Label>
                        <Input id="edit-password_confirmation" v-model="editForm.password_confirmation"
                            type="password" placeholder="Konfirmasi password baru" :disabled="loading" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit-role">Peran</Label>
                        <Select v-model="editForm.role" :disabled="loading">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in props.roles" :key="role.value" :value="role.value">
                                    {{ role.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="errors.role" class="text-sm text-destructive">
                            {{ errors.role }}
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

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Hapus Pengguna</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus pengguna <strong>{{ userToDelete?.name }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                    <Button type="button" variant="outline" @click="cancelDelete">
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" @click="deleteUser">
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
