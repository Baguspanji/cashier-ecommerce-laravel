import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export interface Product {
    id: number
    name: string
    description?: string
    category_id: number
    category: {
        id: number
        name: string
    }
    price: string
    image_path?: string
    current_stock: number
    minimum_stock: number
    is_active: boolean
    created_at: string
    updated_at: string
}

export interface ProductData {
    name: string
    description?: string
    category_id: number
    price: number
    current_stock: number
    minimum_stock: number
    is_active?: boolean
}

export interface ProductFilters {
    search?: string
    category_id?: number
    status?: string
}

export const useProducts = () => {
    const loading = ref(false)
    const errors = ref<Record<string, string>>({})

    const store = (data: ProductData) => {
        loading.value = true
        errors.value = {}

        router.post(route('products.store'), data as any, {
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

    const update = (id: number, data: ProductData) => {
        loading.value = true
        errors.value = {}

        router.patch(route('products.update', id), data as any, {
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
        router.delete(route('products.destroy', id))
    }

    const toggleStatus = (id: number) => {
        router.patch(route('products.toggle-status', id))
    }

    const visitIndex = (filters?: ProductFilters) => {
        router.get(route('products.index'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    const visitShow = (id: number) => {
        router.get(route('products.show', id))
    }

    const visitCreate = () => {
        router.get(route('products.create'))
    }

    const visitEdit = (id: number) => {
        router.get(route('products.edit', id))
    }

    return {
        loading,
        errors,
        store,
        update,
        destroy,
        toggleStatus,
        visitIndex,
        visitShow,
        visitCreate,
        visitEdit,
    }
}
