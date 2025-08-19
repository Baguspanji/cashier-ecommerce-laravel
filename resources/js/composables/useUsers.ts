import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

export interface User {
    id: number
    name: string
    email: string
    role: string
    created_at: string
}

export interface UserData {
    name: string
    email: string
    password: string
    password_confirmation: string
    role: string
}

export interface UserFilters {
    search?: string
    role?: string
}

export const useUsers = () => {
    const loading = ref(false)
    const errors = ref<Record<string, string>>({})

    const store = (data: UserData) => {
        loading.value = true
        errors.value = {}

        router.post(route('users.store'), data as any, {
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

    const update = (id: number, data: UserData) => {
        loading.value = true
        errors.value = {}

        router.patch(route('users.update', id), data as any, {
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
        router.delete(route('users.destroy', id))
    }

    const visitIndex = (filters?: UserFilters) => {
        router.get(route('users.index'), filters as any, {
            preserveState: true,
            replace: true,
        })
    }

    const visitShow = (id: number) => {
        router.get(route('users.show', id))
    }

    const visitCreate = () => {
        router.get(route('users.create'))
    }

    const visitEdit = (id: number) => {
        router.get(route('users.edit', id))
    }

    return {
        loading,
        errors,
        store,
        update,
        destroy,
        visitIndex,
        visitShow,
        visitCreate,
        visitEdit,
    }
}
