import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ProductMovements from '@/pages/Stock/ProductMovements.vue'

// Mock the composable
const mockUseStock = {
    visitOverview: vi.fn(),
    visitCreate: vi.fn(),
    visitProductMovements: vi.fn(),
}

vi.mock('@/composables/useStock', () => ({
    useStock: () => mockUseStock,
}))

// Mock Inertia components
vi.mock('@inertiajs/vue3', () => ({
    Head: {
        template: '<head><slot /></head>',
    },
    Link: {
        template: '<a><slot /></a>',
        props: ['href'],
    },
}))

// Mock layout components
vi.mock('@/layouts/AppLayout.vue', () => ({
    default: {
        template: '<div class="app-layout"><slot /></div>',
    },
}))

vi.mock('@/components/AppPageHeader.vue', () => ({
    default: {
        template: '<div class="page-header"><slot name="actions" /></div>',
        props: ['title', 'description'],
    },
}))

// Mock UI components
vi.mock('@/components/ui/button', () => ({
    Button: {
        template: '<button><slot /></button>',
    },
}))

vi.mock('@/components/ui/card', () => ({
    Card: {
        template: '<div class="card"><slot /></div>',
    },
    CardContent: {
        template: '<div class="card-content"><slot /></div>',
    },
    CardHeader: {
        template: '<div class="card-header"><slot /></div>',
    },
    CardTitle: {
        template: '<div class="card-title"><slot /></div>',
    },
}))

vi.mock('@/components/ui/badge', () => ({
    Badge: {
        template: '<span class="badge"><slot /></span>',
        props: ['variant'],
    },
}))

describe('ProductMovements', () => {
    const defaultProps = {
        product: {
            id: 1,
            name: 'Test Product',
            description: 'Test Description',
            price: '100000',
            minimum_stock: 10,
            current_stock: 50,
            is_active: true,
            category_id: 1,
            category: {
                id: 1,
                name: 'Test Category',
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
            created_at: '2025-01-01T00:00:00.000000Z',
            updated_at: '2025-01-01T00:00:00.000000Z',
        },
        movements: {
            data: [
                {
                    id: 1,
                    type: 'in' as const,
                    quantity: 20,
                    previous_stock: 30,
                    new_stock: 50,
                    reference_type: 'manual',
                    notes: 'Test stock in',
                    user: {
                        id: 1,
                        name: 'Test User',
                        email: 'test@example.com',
                    },
                    created_at: '2025-01-01T12:00:00.000000Z',
                    updated_at: '2025-01-01T12:00:00.000000Z',
                },
                {
                    id: 2,
                    type: 'out' as const,
                    quantity: 5,
                    previous_stock: 55,
                    new_stock: 50,
                    reference_type: 'transaction',
                    notes: 'Test stock out',
                    user: {
                        id: 1,
                        name: 'Test User',
                        email: 'test@example.com',
                    },
                    created_at: '2025-01-01T10:00:00.000000Z',
                    updated_at: '2025-01-01T10:00:00.000000Z',
                },
            ],
            meta: {
                total: 2,
                per_page: 15,
                current_page: 1,
                last_page: 1,
                from: 1,
                to: 2,
                path: '/stock/products/1/movements',
                has_more_pages: false,
            },
        },
    }

    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })
        expect(wrapper.exists()).toBe(true)
    })

    it('displays product information', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        expect(wrapper.text()).toContain('Test Product')
        expect(wrapper.text()).toContain('Test Category')
        expect(wrapper.text()).toContain('50') // current stock
        expect(wrapper.text()).toContain('10') // minimum stock
    })

    it('displays stock movements', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        expect(wrapper.text()).toContain('Stok Masuk')
        expect(wrapper.text()).toContain('Stok Keluar')
        expect(wrapper.text()).toContain('Test stock in')
        expect(wrapper.text()).toContain('Test stock out')
        expect(wrapper.text()).toContain('Test User')
    })

    it('shows movement types correctly', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        expect(wrapper.text()).toContain('Stok Masuk') // for 'in' type
        expect(wrapper.text()).toContain('Stok Keluar') // for 'out' type
    })

    it('shows stock changes correctly', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        // Should show stock changes like "30 → 50 (+20)" and "55 → 50 (-5)"
        expect(wrapper.text()).toContain('30 → 50')
        expect(wrapper.text()).toContain('55 → 50')
    })

    it('shows empty state when no movements', () => {
        const emptyProps = {
            ...defaultProps,
            movements: {
                data: [],
                meta: {
                    total: 0,
                    per_page: 15,
                    current_page: 1,
                    last_page: 1,
                    from: null,
                    to: null,
                    path: '/stock/products/1/movements',
                    has_more_pages: false,
                },
            },
        }

        const wrapper = mount(ProductMovements, {
            props: emptyProps,
        })

        expect(wrapper.text()).toContain('Belum ada pergerakan stok')
    })

    it('calls visitOverview when back button is clicked', async () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        const backButton = wrapper.find('button')
        if (backButton.exists()) {
            await backButton.trigger('click')
            expect(mockUseStock.visitOverview).toHaveBeenCalled()
        }
    })

    it('formats dates correctly', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        // Should contain formatted date (Indonesian format)
        expect(wrapper.text()).toMatch(/1 Jan 2025/)
    })

    it('shows reference type labels correctly', () => {
        const wrapper = mount(ProductMovements, {
            props: defaultProps,
        })

        expect(wrapper.text()).toContain('Manual')
        expect(wrapper.text()).toContain('Transaksi')
    })
})
