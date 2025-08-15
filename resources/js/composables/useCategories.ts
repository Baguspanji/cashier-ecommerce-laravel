import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export interface Category {
    id: number
    name: string
    description?: string
    products_count?: number
    created_at: string
    updated_at: string
}

export interface CategoryData {
    name: string
    description?: string
}

export interface CategoryFilters {
    search?: string
}

export const useCategories = () => {
    const loading = ref(false)
    const errors = ref<Record<string, string>>({})

    const store = (data: CategoryData) => {
        loading.value = true
        errors.value = {}

        router.post(route('categories.store'), data as any, {
            onStart: () => {
                loading.value = true
            },
            onFinish: () => {
                loading.value = false
            },
            onError: (error) => {
                errors.value = error as Record<string, string>
            },
        })
    }

    const update = (id: number, data: CategoryData) => {
        loading.value = true
        errors.value = {}

        router.patch(route('categories.update', id), data as any, {
            onStart: () => {
                loading.value = true
            },
            onFinish: () => {
                loading.value = false
            },
            onError: (error) => {
                errors.value = error as Record<string, string>
            },
        })
    }

    const destroy = (id: number) => {
        router.delete(route('categories.destroy', id), {
            onBefore: () => confirm('Apakah Anda yakin ingin menghapus kategori ini?'),
        })
    }

    const visitIndex = (filters?: CategoryFilters) => {
        router.get(route('categories.index'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    return {
        loading,
        errors,
        store,
        update,
        destroy,
        visitIndex,
    }
}
