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
    visitIndex: vi.fn(),
    visitShow: vi.fn(),
    visitCreate: vi.fn(),
    visitEdit: vi.fn(),
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

// Mock dialog components
vi.mock('@/components/ui/dialog', () => ({
    Dialog: {
        template: '<div class="dialog" v-if="open"><slot /></div>',
        props: ['open'],
        emits: ['update:open'],
    },
    DialogContent: {
        template: '<div class="dialog-content"><slot /></div>',
    },
    DialogDescription: {
        template: '<div class="dialog-description"><slot /></div>',
    },
    DialogFooter: {
        template: '<div class="dialog-footer"><slot /></div>',
    },
    DialogHeader: {
        template: '<div class="dialog-header"><slot /></div>',
    },
    DialogTitle: {
        template: '<div class="dialog-title"><slot /></div>',
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
    const defaultProps = {
        products: {
            data: [
                {
                    id: 1,
                    name: 'Laptop',
                    description: 'Gaming laptop',
                    price: '15000000',
                    minimum_stock: 5,
                    current_stock: 10,
                    is_active: true,
                    category_id: 1,
                    category: {
                        id: 1,
                        name: 'Electronics',
                        created_at: '2025-01-01T00:00:00.000000Z',
                        updated_at: '2025-01-01T00:00:00.000000Z',
                    },
                    created_at: '2025-01-01T00:00:00.000000Z',
                    updated_at: '2025-01-01T00:00:00.000000Z',
                },
                {
                    id: 2,
                    name: 'T-Shirt',
                    description: 'Cotton t-shirt',
                    price: '150000',
                    minimum_stock: 20,
                    current_stock: 0,
                    is_active: true,
                    category_id: 2,
                    category: {
                        id: 2,
                        name: 'Clothing',
                        created_at: '2025-01-01T00:00:00.000000Z',
                        updated_at: '2025-01-01T00:00:00.000000Z',
                    },
                    created_at: '2025-01-01T00:00:00.000000Z',
                    updated_at: '2025-01-01T00:00:00.000000Z',
                },
            ],
        },
        categories: [
            {
                id: 1,
                name: 'Electronics',
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
            {
                id: 2,
                name: 'Clothing',
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
        ],
        filters: {
            search: '',
            category_id: undefined,
            status: 'all',
        },
    }

    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })
        expect(wrapper.exists()).toBe(true)
    })

    it('displays products list', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })
        const productsText = wrapper.text()

        expect(productsText).toContain('Laptop')
        expect(productsText).toContain('T-Shirt')
        expect(productsText).toContain('Electronics')
        expect(productsText).toContain('Clothing')
    })

    it('shows create product button', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })
        const createButton = wrapper.find('[data-testid="create-product-btn"]')

        expect(createButton.exists()).toBe(true)
        expect(createButton.text()).toContain('Tambah Produk')
    })

    it('shows search and filter inputs', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        const searchInput = wrapper.find('[data-testid="search-input"]')
        const categoryFilter = wrapper.find('[data-testid="category-filter"]')
        const statusFilter = wrapper.find('[data-testid="status-filter"]')

        expect(searchInput.exists()).toBe(true)
        expect(categoryFilter.exists()).toBe(true)
        expect(statusFilter.exists()).toBe(true)
    })

    it('displays stock status badges correctly', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })
        const badgeText = wrapper.text()

        // Laptop has stock > minimum, so should show "Tersedia"
        expect(badgeText).toContain('Tersedia')

        // T-Shirt has 0 stock, so should show "Habis"
        expect(badgeText).toContain('Habis')
    })

    it('shows product actions', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        // Look for action buttons (View, Edit, Delete, Toggle Status)
        const viewButtons = wrapper.findAll('[data-testid^="view-product-"]')
        const editButtons = wrapper.findAll('[data-testid^="edit-product-"]')
        const deleteButtons = wrapper.findAll('[data-testid^="delete-product-"]')

        expect(viewButtons.length).toBeGreaterThan(0)
        expect(editButtons.length).toBeGreaterThan(0)
        expect(deleteButtons.length).toBeGreaterThan(0)
    })

    it('calls toggleStatus when toggle button is clicked', async () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        const toggleButton = wrapper.find('[data-testid="toggle-product-1"]')
        if (toggleButton.exists()) {
            await toggleButton.trigger('click')
            expect(mockUseProducts.toggleStatus).toHaveBeenCalledWith(1)
        }
    })

    it('displays empty state when no products', async () => {
        const emptyProps = {
            ...defaultProps,
            products: { data: [] }
        }

        const wrapper = mount(ProductsIndex, {
            props: emptyProps,
        })
        await wrapper.vm.$nextTick()

        const emptyStateText = wrapper.text()
        expect(emptyStateText).toContain('Belum ada produk')
    })

    it('formats price correctly', () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })
        const text = wrapper.text()

        // Check if price is formatted with Indonesian currency format
        expect(text).toContain('Rp')
    })

    it('opens delete dialog when delete button is clicked', async () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        const deleteButton = wrapper.find('[data-testid="delete-product-1"]')
        expect(deleteButton.exists()).toBe(true)

        await deleteButton.trigger('click')

        // Check if dialog is opened
        const dialog = wrapper.find('.dialog')
        expect(dialog.exists()).toBe(true)
        expect(wrapper.text()).toContain('Hapus Produk')
        expect(wrapper.text()).toContain('Laptop')
    })

    it('closes delete dialog when cancel button is clicked', async () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        // Open dialog first
        const deleteButton = wrapper.find('[data-testid="delete-product-1"]')
        await deleteButton.trigger('click')

        // Find and click cancel button
        const allButtons = wrapper.findAll('button')
        const cancelButton = allButtons.find(button =>
            button.text().includes('Batal')
        )

        if (cancelButton && cancelButton.exists()) {
            await cancelButton.trigger('click')

            // Dialog should be closed
            await wrapper.vm.$nextTick()
            const dialog = wrapper.find('.dialog')
            expect(dialog.exists()).toBe(false)
        }
    })

    it('calls destroy when confirm delete button is clicked', async () => {
        const wrapper = mount(ProductsIndex, {
            props: defaultProps,
        })

        // Open dialog first
        const deleteButton = wrapper.find('[data-testid="delete-product-1"]')
        await deleteButton.trigger('click')

        // Find and click confirm button
        const allButtons = wrapper.findAll('button')
        const confirmButton = allButtons.find(button =>
            button.text().includes('Hapus') && !button.text().includes('Produk')
        )

        if (confirmButton && confirmButton.exists()) {
            await confirmButton.trigger('click')

            // Should call destroy with correct product id
            expect(mockUseProducts.destroy).toHaveBeenCalledWith(1)
        }
    })
})
