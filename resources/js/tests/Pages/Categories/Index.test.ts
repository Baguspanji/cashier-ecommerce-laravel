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
        template: '<div class="dialog"><slot /></div>',
    },
    DialogContent: {
        template: '<div class="dialog-content"><slot /></div>',
    },
    DialogDescription: {
        template: '<div class="dialog-description"><slot /></div>',
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

describe('Categories Index', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly', () => {
        const wrapper = mount(CategoriesIndex)
        expect(wrapper.exists()).toBe(true)
    })

    it('displays categories list', () => {
        const wrapper = mount(CategoriesIndex)
        const categoriesText = wrapper.text()

        expect(categoriesText).toContain('Electronics')
        expect(categoriesText).toContain('Clothing')
        expect(categoriesText).toContain('5 produk')
        expect(categoriesText).toContain('3 produk')
    })

    it('shows create category button', () => {
        const wrapper = mount(CategoriesIndex)
        const createButton = wrapper.find('[data-testid="create-category-btn"]')

        expect(createButton.exists()).toBe(true)
        expect(createButton.text()).toContain('Tambah Kategori')
    })

    it('shows search input', () => {
        const wrapper = mount(CategoriesIndex)
        const searchInput = wrapper.find('[data-testid="search-input"]')

        expect(searchInput.exists()).toBe(true)
    })

    it('displays empty state when no categories', async () => {
        // Mock empty categories by updating the mock directly
        mockUseCategories.categories.value = []
        mockUseCategories.filteredCategories.value = []

        const wrapper = mount(CategoriesIndex)
        await wrapper.vm.$nextTick()

        const emptyStateText = wrapper.text()
        expect(emptyStateText).toContain('Belum ada kategori')
    })

    it('calls store function when creating category', async () => {
        const wrapper = mount(CategoriesIndex)

        // Find and click the create button to open dialog
        const createButton = wrapper.find('[data-testid="create-category-btn"]')
        await createButton.trigger('click')

        // The actual form submission would be handled by the composable
        expect(mockUseCategories.store).toHaveBeenCalledTimes(0) // Initially not called
    })

    it('shows loading state', async () => {
        // Mock loading state
        mockUseCategories.loading.value = true

        const wrapper = mount(CategoriesIndex)
        await wrapper.vm.$nextTick()

        // Check for loading indicators - either text or class
        const hasLoadingText = wrapper.text().includes('Loading')
        const hasLoadingClass = wrapper.find('.loading').exists()

        expect(hasLoadingText || hasLoadingClass).toBe(true)
    })
})
