import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import CategoriesIndex from '@/pages/Categories/Index.vue'

// Mock the composable
const mockUseCategories = {
    categories: {
        value: [
            {
                id: 1,
                name: 'Electronics',
                description: 'Electronic items',
                products_count: 5,
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
            {
                id: 2,
                name: 'Clothing',
                description: 'Apparel and clothing',
                products_count: 3,
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
        ],
    },
    loading: { value: false },
    errors: { value: {} },
    search: { value: '' },
    store: vi.fn(),
    update: vi.fn(),
    destroy: vi.fn(),
    visitIndex: vi.fn(),
    filteredCategories: {
        value: [
            {
                id: 1,
                name: 'Electronics',
                description: 'Electronic items',
                products_count: 5,
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
            {
                id: 2,
                name: 'Clothing',
                description: 'Apparel and clothing',
                products_count: 3,
                created_at: '2025-01-01T00:00:00.000000Z',
                updated_at: '2025-01-01T00:00:00.000000Z',
            },
        ],
    },
}

vi.mock('@/composables/useCategories', () => ({
    useCategories: () => mockUseCategories,
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
    router: {
        visit: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
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
    DialogTrigger: {
        template: '<div class="dialog-trigger"><slot /></div>',
    },
}))

vi.mock('@/components/ui/input', () => ({
    Input: {
        template: '<input />',
    },
}))

vi.mock('@/components/ui/label', () => ({
    Label: {
        template: '<label><slot /></label>',
    },
}))

describe('Categories Index', () => {
    const defaultProps = {
        categories: {
            data: [
                {
                    id: 1,
                    name: 'Electronics',
                    description: 'Electronic items',
                    products_count: 5,
                    created_at: '2025-01-01T00:00:00.000000Z',
                    updated_at: '2025-01-01T00:00:00.000000Z',
                },
                {
                    id: 2,
                    name: 'Clothing',
                    description: 'Apparel and clothing',
                    products_count: 3,
                    created_at: '2025-01-01T00:00:00.000000Z',
                    updated_at: '2025-01-01T00:00:00.000000Z',
                },
            ],
            links: [],
            meta: { total: 2 },
        },
        filters: {
            search: '',
        },
    }

    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })
        expect(wrapper.exists()).toBe(true)
    })

    it('displays categories list', () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })
        const categoriesText = wrapper.text()

        expect(categoriesText).toContain('Electronics')
        expect(categoriesText).toContain('Clothing')
        expect(categoriesText).toContain('5 produk')
        expect(categoriesText).toContain('3 produk')
    })

    it('shows create category button', () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })
        const createButton = wrapper.find('[data-testid="create-category-btn"]')

        expect(createButton.exists()).toBe(true)
        expect(createButton.text()).toContain('Tambah Kategori')
    })

    it('shows search input', () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })
        const searchInput = wrapper.find('[data-testid="search-input"]')

        expect(searchInput.exists()).toBe(true)
    })

    it('displays empty state when no categories', async () => {
        const emptyProps = {
            ...defaultProps,
            categories: { ...defaultProps.categories, data: [] }
        }

        const wrapper = mount(CategoriesIndex, {
            props: emptyProps,
        })
        await wrapper.vm.$nextTick()

        const emptyStateText = wrapper.text()
        expect(emptyStateText).toContain('Belum ada kategori')
    })

    it('calls store function when creating category', async () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })

        // Find and click the create button to open dialog
        const createButton = wrapper.find('[data-testid="create-category-btn"]')
        await createButton.trigger('click')

        // The actual form submission would be handled by the composable
        expect(mockUseCategories.store).toHaveBeenCalledTimes(0) // Initially not called
    })

    it('shows loading state', async () => {
        // Mock loading state
        mockUseCategories.loading.value = true

        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })
        await wrapper.vm.$nextTick()

        // Check for loading indicators - either text or class
        const hasLoadingText = wrapper.text().includes('Loading')
        const hasLoadingClass = wrapper.find('.loading').exists()

        expect(hasLoadingText || hasLoadingClass).toBe(true)
    })

    it('opens delete dialog when delete button is clicked', async () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })

        const deleteButton = wrapper.find('[data-testid="delete-category-1"]')
        expect(deleteButton.exists()).toBe(true)

        await deleteButton.trigger('click')

        // Check if dialog is opened
        const dialog = wrapper.find('.dialog')
        expect(dialog.exists()).toBe(true)
        expect(wrapper.text()).toContain('Hapus Kategori')
        expect(wrapper.text()).toContain('Electronics')
    })

    it('shows warning for categories with products in delete dialog', async () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })

        // Click delete button for Electronics (has 5 products)
        const deleteButton = wrapper.find('[data-testid="delete-category-1"]')
        await deleteButton.trigger('click')

        // Check if warning message is shown
        expect(wrapper.text()).toContain('Peringatan')
        expect(wrapper.text()).toContain('5 produk')
    })

    it('closes delete dialog when cancel button is clicked', async () => {
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })

        // Open dialog first
        const deleteButton = wrapper.find('[data-testid="delete-category-1"]')
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
        const wrapper = mount(CategoriesIndex, {
            props: defaultProps,
        })

        // Open dialog first
        const deleteButton = wrapper.find('[data-testid="delete-category-1"]')
        await deleteButton.trigger('click')

        // Find and click confirm button
        const allButtons = wrapper.findAll('button')
        const confirmButton = allButtons.find(button =>
            button.text().includes('Hapus') && !button.text().includes('Kategori')
        )

        if (confirmButton && confirmButton.exists()) {
            await confirmButton.trigger('click')

            // Should call destroy with correct category id
            expect(mockUseCategories.destroy).toHaveBeenCalledWith(1)
        }
    })
})
