<script setup lang="ts">
import { reactive } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { useProducts, type Product, type ProductData } from '@/composables/useProducts'
import { type Category } from '@/composables/useCategories'
import {
    ArrowLeftIcon,
    SaveIcon,
    PackageIcon,
    DollarSignIcon,
    ToggleLeftIcon
} from 'lucide-vue-next'

interface Props {
    product: Product
    categories: Category[]
}

const props = defineProps<Props>()

const { loading, errors, update, visitShow, visitIndex } = useProducts()

// Form data
const form = reactive<ProductData>({
    name: props.product.name,
    description: props.product.description || '',
    category_id: props.product.category_id,
    price: parseFloat(props.product.price),
    current_stock: props.product.current_stock,
    minimum_stock: props.product.minimum_stock,
    is_active: props.product.is_active,
})

const handleSubmit = () => {
    update(props.product.id, form)
}

const handleCancel = () => {
    visitShow(props.product.id)
}
</script>

<template>
    <Head :title="`Edit Product - ${product.name}`" />

    <AppLayout>
        <div class="container mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="visitIndex()"
                        class="gap-2"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Products
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                        <p class="text-sm text-gray-500">{{ product.name }}</p>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Product Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Product Name -->
                                <div class="space-y-2">
                                    <Label for="name">Product Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter product name"
                                        required
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.name
                                        }"
                                    />
                                    <p v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</p>
                                </div>

                                <!-- Category -->
                                <div class="space-y-2">
                                    <Label for="category_id">Category *</Label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger
                                            :class="{
                                                'border-red-300 focus:border-red-500 focus:ring-red-500': errors.category_id
                                            }"
                                        >
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="category in categories"
                                                :key="category.id"
                                                :value="category.id.toString()"
                                            >
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="errors.category_id" class="text-sm text-red-600">{{ errors.category_id }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Enter product description (optional)"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': errors.description
                                    }"
                                />
                                <p v-if="errors.description" class="text-sm text-red-600">{{ errors.description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Pricing -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <DollarSignIcon class="h-5 w-5" />
                                Pricing Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                                <!-- Price -->
                                <div class="space-y-2">
                                    <Label for="price">Product Price (IDR) *</Label>
                                    <Input
                                        id="price"
                                        v-model.number="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.price
                                        }"
                                    />
                                    <p v-if="errors.price" class="text-sm text-red-600">{{ errors.price }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Stock Management -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <PackageIcon class="h-5 w-5" />
                                Stock Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Stock -->
                                <div class="space-y-2">
                                    <Label for="current_stock">Current Stock *</Label>
                                    <Input
                                        id="current_stock"
                                        v-model.number="form.current_stock"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        required
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.current_stock
                                        }"
                                    />
                                    <p v-if="errors.current_stock" class="text-sm text-red-600">{{ errors.current_stock }}</p>
                                </div>

                                <!-- Minimum Stock -->
                                <div class="space-y-2">
                                    <Label for="minimum_stock">Minimum Stock *</Label>
                                    <Input
                                        id="minimum_stock"
                                        v-model.number="form.minimum_stock"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        required
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.minimum_stock
                                        }"
                                    />
                                    <p v-if="errors.minimum_stock" class="text-sm text-red-600">{{ errors.minimum_stock }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status & Additional Settings -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <ToggleLeftIcon class="h-5 w-5" />
                                Status & Settings
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <!-- Status Toggle -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Label for="is_active">Product Status</Label>
                                        <p class="text-sm text-gray-500">
                                            {{ form.is_active ? 'Product is active and available for sale' : 'Product is inactive and hidden from customers' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_active"
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        />
                                        <Label for="is_active" class="font-medium">
                                            {{ form.is_active ? 'Active' : 'Inactive' }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-6">
                        <Button
                            type="submit"
                            :disabled="loading"
                            class="gap-2"
                        >
                            <SaveIcon class="h-4 w-4" />
                            {{ loading ? 'Updating...' : 'Update Product' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="handleCancel"
                            :disabled="loading"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
