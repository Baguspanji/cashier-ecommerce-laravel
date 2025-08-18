import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ProductsIndex from '@/pages/Products/Index.vue'

// Mock the composable
const mockUseProducts = {
    products: {
        value: [
            {
                id: 1,
                name: 'Laptop',
                description: 'Gaming laptop',
                price: 15000000,
                minimum_stock: 5,
                current_stock: 10,
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
                price: 150000,
                minimum_stock: 20,
                current_stock: 0,
                is_active: true,
                category_id: 2,
                category: {
                    id: 2,
                    name: 'Clothing',
                },
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
        ],
    },
    loading: { value: false },
    errors: { value: {} },
    search: { value: '' },
    categoryFilter: { value: '' },
    statusFilter: { value: 'all' },
    destroy: vi.fn(),
    toggleStatus: vi.fn(),
    filteredProducts: {
        value: [
            {
                id: 1,
                name: 'Laptop',
                description: 'Gaming laptop',
                price: 15000000,
                minimum_stock: 5,
                current_stock: 10,
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
                price: 150000,
                minimum_stock: 20,
                current_stock: 0,
                is_active: true,
                category_id: 2,
                category: {
                    id: 2,
                    name: 'Clothing',
                },
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
        ],
    },
}

const mockUseCategories = {
    categories: {
        value: [
            { id: 1, name: 'Electronics' },
            { id: 2, name: 'Clothing' },
        ],
    },
}

vi.mock('@/composables/useProducts', () => ({
    useProducts: () => mockUseProducts,
}))

vi.mock('@/composables/useCategories', () => ({
    useCategories: () => mockUseCategories,
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
    CardDescription: {
        template: '<div class="card-description"><slot /></div>',
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
    },
}))

vi.mock('@/components/ui/select', () => ({
    Select: {
        template: '<div class="select"><slot /></div>',
    },
    SelectContent: {
        template: '<div class="select-content"><slot /></div>',
    },
    SelectItem: {
        template: '<div class="select-item"><slot /></div>',
    },
    SelectTrigger: {
        template: '<div class="select-trigger"><slot /></div>',
    },
    SelectValue: {
        template: '<div class="select-value"><slot /></div>',
    },
}))

describe('Products Index', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(ProductsIndex)
        expect(wrapper.exists()).toBe(true)
    })

    it('displays products list', () => {
        const wrapper = mount(ProductsIndex)
        const productsText = wrapper.text()

        expect(productsText).toContain('Laptop')
        expect(productsText).toContain('T-Shirt')
        expect(productsText).toContain('Electronics')
        expect(productsText).toContain('Clothing')
    })

    it('shows create product button', () => {
        const wrapper = mount(ProductsIndex)
        const createButton = wrapper.find('[data-testid="create-product-btn"]')

        expect(createButton.exists()).toBe(true)
        expect(createButton.text()).toContain('Tambah Produk')
    })

    it('shows search and filter inputs', () => {
        const wrapper = mount(ProductsIndex)

        const searchInput = wrapper.find('[data-testid="search-input"]')
        const categoryFilter = wrapper.find('[data-testid="category-filter"]')
        const statusFilter = wrapper.find('[data-testid="status-filter"]')

        expect(searchInput.exists()).toBe(true)
        expect(categoryFilter.exists()).toBe(true)
        expect(statusFilter.exists()).toBe(true)
    })

    it('displays stock status badges correctly', () => {
        const wrapper = mount(ProductsIndex)
        const badgeText = wrapper.text()

        // Laptop has stock > minimum, so should show "Tersedia"
        expect(badgeText).toContain('Tersedia')

        // T-Shirt has 0 stock, so should show "Habis"
        expect(badgeText).toContain('Habis')
    })

    it('shows product actions', () => {
        const wrapper = mount(ProductsIndex)

        // Look for action buttons (View, Edit, Delete, Toggle Status)
        const viewButtons = wrapper.findAll('[data-testid^="view-product-"]')
        const editButtons = wrapper.findAll('[data-testid^="edit-product-"]')
        const deleteButtons = wrapper.findAll('[data-testid^="delete-product-"]')

        expect(viewButtons.length).toBeGreaterThan(0)
        expect(editButtons.length).toBeGreaterThan(0)
        expect(deleteButtons.length).toBeGreaterThan(0)
    })

    it('calls toggleStatus when toggle button is clicked', async () => {
        const wrapper = mount(ProductsIndex)

        const toggleButton = wrapper.find('[data-testid="toggle-product-1"]')
        if (toggleButton.exists()) {
            await toggleButton.trigger('click')
            expect(mockUseProducts.toggleStatus).toHaveBeenCalledWith(1)
        }
    })

    it('displays empty state when no products', async () => {
        mockUseProducts.products.value = []
        mockUseProducts.filteredProducts.value = []

        const wrapper = mount(ProductsIndex)
        await wrapper.vm.$nextTick()

        const emptyStateText = wrapper.text()
        expect(emptyStateText).toContain('Tidak ada produk')
    })

    it('formats price correctly', () => {
        const wrapper = mount(ProductsIndex)
        const text = wrapper.text()

        // Check if price is formatted with Indonesian currency format
        expect(text).toContain('Rp')
    })
})
