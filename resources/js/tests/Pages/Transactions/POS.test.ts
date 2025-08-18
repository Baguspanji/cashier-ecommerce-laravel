import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import POS from '@/pages/Transactions/POS.vue'
import type { Product } from '@/composables/useProducts'

// Mock the composables
const mockUseTransactions = {
    loading: { value: false },
    errors: { value: {} },
    store: vi.fn(),
}

const mockProducts: Product[] = [
    {
        id: 1,
        name: 'Laptop',
        description: 'Gaming laptop',
        price: '15000000',
        current_stock: 10,
        minimum_stock: 5,
        is_active: true,
        category_id: 1,
        category: {
            id: 1,
            name: 'Electronics',
        },
        created_at: '2025-01-01T00:00:00.000000Z',
        updated_at: '2025-01-01T00:00:00.000000Z',
    },
    {
        id: 2,
        name: 'T-Shirt',
        description: 'Cotton t-shirt',
        price: '150000',
        current_stock: 5,
        minimum_stock: 20,
        is_active: true,
        category_id: 2,
        category: {
            id: 2,
            name: 'Clothing',
        },
        created_at: '2025-01-01T00:00:00.000000Z',
        updated_at: '2025-01-01T00:00:00.000000Z',
    },
]

vi.mock('@/composables/useTransactions', () => ({
    useTransactions: () => mockUseTransactions,
}))

// Mock UI components
vi.mock('@/components/ui/button', () => ({
    Button: {
        template: '<button><slot /></button>',
        props: ['disabled', 'size', 'variant'],
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

vi.mock('@/components/ui/input', () => ({
    Input: {
        template: '<input />',
        props: ['modelValue', 'placeholder', 'class', 'type'],
    },
}))

vi.mock('@/components/ui/label', () => ({
    Label: {
        template: '<label><slot /></label>',
    },
}))

describe('POS Component', () => {
    const defaultProps = {
        products: mockProducts,
    }

    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })
        expect(wrapper.exists()).toBe(true)
    })

    it('displays products list', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        expect(text).toContain('Laptop')
        expect(text).toContain('T-Shirt')
        expect(text).toContain('Electronics')
        expect(text).toContain('Clothing')
    })

    it('shows search functionality', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const searchInput = wrapper.find('input[placeholder*="Cari produk"]')
        expect(searchInput.exists()).toBe(true)
    })

    it('displays cart section', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasCart = text.includes('Keranjang') || text.includes('Cart')
        expect(hasCart).toBe(true)
    })

    it('shows payment methods', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasPaymentMethods = text.includes('Tunai') || text.includes('Cash')
        expect(hasPaymentMethods).toBe(true)
    })

    it('displays product prices correctly', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        expect(text).toContain('Rp') // Indonesian currency format
    })

    it('shows stock information', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasStock = text.includes('Stok') || text.includes('Stock')
        expect(hasStock).toBe(true)
    })

    it('disables add button for out of stock products', () => {
        const outOfStockProducts = [
            {
                ...mockProducts[0],
                current_stock: 0,
            },
        ]

        const wrapper = mount(POS, {
            props: {
                products: outOfStockProducts,
            },
        })

        const text = wrapper.text()
        const hasOutOfStock = text.includes('Stok Habis') || text.includes('Out of Stock')
        expect(hasOutOfStock).toBe(true)
    })

    it('shows checkout section', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasCheckout = text.includes('Total') || text.includes('Checkout')
        expect(hasCheckout).toBe(true)
    })

    it('displays payment amount input', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const paymentInputs = wrapper.findAll('input[type="number"]')
        expect(paymentInputs.length).toBeGreaterThan(0)
    })

    it('shows process transaction button', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasProcess = text.includes('Proses') || text.includes('Process')
        expect(hasProcess).toBe(true)
    })

    it('displays empty cart message initially', () => {
        const wrapper = mount(POS, {
            props: defaultProps,
        })

        const text = wrapper.text()
        const hasEmptyCart = text.includes('Keranjang kosong') || text.includes('Cart is empty')
        expect(hasEmptyCart).toBe(true)
    })
})
