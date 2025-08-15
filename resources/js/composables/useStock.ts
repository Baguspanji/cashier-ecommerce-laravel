import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export interface StockMovement {
    id: number
    product_id: number
    product: {
        id: number
        name: string
        category: {
            name: string
        }
    }
    user_id: number
    user: {
        name: string
    }
    type: 'in' | 'out' | 'adjustment'
    quantity: number
    reference_id?: number
    reference_type?: string
    notes?: string
    created_at: string
}

export interface StockMovementData {
    product_id: number
    type: 'in' | 'out' | 'adjustment'
    quantity: number
    notes?: string
}

export interface BulkStockAdjustmentData {
    adjustments: Array<{
        product_id: number
        new_stock: number
    }>
    notes?: string
}

export interface StockFilters {
    search?: string
    product_id?: number
    type?: string
    date?: string
    date_from?: string
    date_to?: string
}

export const useStock = () => {
    const loading = ref(false)
    const errors = ref<Record<string, string>>({})

    const store = (data: StockMovementData) => {
        loading.value = true
        errors.value = {}

        router.post(route('stock.store'), data as any, {
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

    const bulkAdjustment = (data: BulkStockAdjustmentData) => {
        loading.value = true
        errors.value = {}

        router.post(route('stock.bulk-adjustment'), data as any, {
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

    const visitIndex = (filters?: StockFilters) => {
        router.get(route('stock.index'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    const visitOverview = (filters?: any) => {
        router.get(route('stock.overview'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    const visitCreate = () => {
        router.get(route('stock.create'))
    }

    const visitProductMovements = (productId: number) => {
        router.get(route('stock.product-movements', productId))
    }

    return {
        loading,
        errors,
        store,
        bulkAdjustment,
        visitIndex,
        visitOverview,
        visitCreate,
        visitProductMovements,
    }
}
