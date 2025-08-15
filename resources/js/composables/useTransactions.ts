import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export interface Transaction {
    id: number
    transaction_number: string
    user_id: number
    user: {
        id: number
        name: string
    }
    total_amount: string
    payment_method: 'cash' | 'debit' | 'credit' | 'e-wallet'
    payment_amount: string
    change_amount: string
    status: 'pending' | 'completed' | 'cancelled'
    notes?: string
    items: TransactionItem[]
    created_at: string
    updated_at: string
}

export interface TransactionItem {
    id: number
    transaction_id: number
    product_id: number
    product: {
        id: number
        name: string
        category: {
            name: string
        }
    }
    product_name: string
    quantity: number
    unit_price: string
    subtotal: string
}

export interface CreateTransactionData {
    items: Array<{
        product_id: number
        quantity: number
    }>
    payment_method: 'cash' | 'debit' | 'credit' | 'e-wallet'
    payment_amount: number
    notes?: string
}

export interface TransactionFilters {
    search?: string
    status?: string
    date?: string
    date_from?: string
    date_to?: string
}

export const useTransactions = () => {
    const loading = ref(false)
    const errors = ref<Record<string, string>>({})

    const store = (data: CreateTransactionData) => {
        loading.value = true
        errors.value = {}

        router.post(route('transactions.store'), data as any, {
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

    const cancel = (id: number) => {
        router.post(route('transactions.cancel', id), {}, {
            onBefore: () => confirm('Apakah Anda yakin ingin membatalkan transaksi ini?'),
        })
    }

    const visitIndex = (filters?: TransactionFilters) => {
        router.get(route('transactions.index'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    const visitShow = (id: number) => {
        router.get(route('transactions.show', id))
    }

    const visitPOS = () => {
        router.get(route('transactions.pos'))
    }

    const visitDailyReport = (date?: string) => {
        const params = date ? { date } : {}
        router.get(route('transactions.daily-report'), params as any)
    }

    return {
        loading,
        errors,
        store,
        cancel,
        visitIndex,
        visitShow,
        visitPOS,
        visitDailyReport,
    }
}
