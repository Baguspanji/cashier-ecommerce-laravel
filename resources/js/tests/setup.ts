import { config } from '@vue/test-utils'
import { vi } from 'vitest'

// Mock Inertia.js
const mockInertia = {
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        reload: vi.fn(),
    },
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    id: 1,
                    name: 'Test User',
                    email: 'test@example.com',
                },
            },
        },
        url: '/dashboard',
    })),
    Link: {
        template: '<a><slot /></a>',
    },
    Head: {
        template: '<head><slot /></head>',
    },
}

// Mock route function (Ziggy)
const mockRoute = vi.fn((name: string, params?: any) => {
    const routes: Record<string, string> = {
        'dashboard': '/dashboard',
        'categories.index': '/categories',
        'categories.store': '/categories',
        'categories.update': '/categories/1',
        'categories.destroy': '/categories/1',
        'products.index': '/products',
        'products.create': '/products/create',
        'products.store': '/products',
        'products.show': '/products/1',
        'products.edit': '/products/1/edit',
        'products.update': '/products/1',
        'products.destroy': '/products/1',
        'transactions.index': '/transactions',
        // 'transactions.pos': '/transactions/pos',
        'transactions.store': '/transactions',
        'transactions.show': '/transactions/1',
        'stock.index': '/stock',
        'stock.overview': '/stock/overview',
        'stock.create': '/stock/create',
        'stock.store': '/stock',
    }

    let url = routes[name] || `/${name}`

    if (params && typeof params === 'object') {
        Object.keys(params).forEach(key => {
            url = url.replace(`{${key}}`, params[key])
        })
    }

    return url
})

// Assign to global
;(globalThis as any).route = mockRoute

// Set global config for Vue Test Utils
config.global.mocks = {
    route: mockRoute,
}

config.global.stubs = {
    Link: mockInertia.Link,
    Head: mockInertia.Head,
}

config.global.plugins = []
