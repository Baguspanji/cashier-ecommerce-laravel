<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ShoppingCartIcon, TagIcon, StarIcon } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    description: string;
    price: string;
    image_path: string | null;
    current_stock: number;
    category: {
        id: number;
        name: string;
    };
}

interface Category {
    id: number;
    name: string;
    description: string | null;
    products_count: number;
}

interface Props {
    categories: Category[];
    featuredProducts: Product[];
    productsByCategory: Record<number, Product[]>;
}

const props = defineProps<Props>();
const page = usePage();

const selectedCategory = ref<number | null>(null);

const formatPrice = (price: string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(parseFloat(price));
};

const filteredProducts = computed(() => {
    if (selectedCategory.value === null) {
        return props.featuredProducts;
    }
    return props.productsByCategory[selectedCategory.value] || [];
});

const selectCategory = (categoryId: number | null) => {
    selectedCategory.value = categoryId;
};

const isAuthenticated = computed(() => {
    return !!(page.props as any).auth?.user;
});
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
        <!-- Header -->
        <header
            class="w-full bg-white/80 backdrop-blur-sm border-b border-gray-200 dark:bg-gray-900/80 dark:border-gray-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2">
                        <ShoppingCartIcon class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Cashier Store</h1>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex items-center space-x-4">
                        <Link v-if="isAuthenticated" :href="route('dashboard')"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')"
                                class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors dark:text-gray-300 dark:hover:text-blue-400">
                            Masuk
                            </Link>
                            <!-- <Link :href="route('register')"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Daftar
                            </Link> -->
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                        Selamat Datang di
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                            Cashier Store
                        </span>
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
                        Temukan produk terbaik dengan harga terjangkau. Kelola toko Anda dengan mudah menggunakan sistem
                        kasir
                        modern kami.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Button v-if="!isAuthenticated" as-child size="lg"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 w-64">
                            <Link :href="route('login')">
                            Mulai Sekarang
                            </Link>
                        </Button>
                        <Button v-else as-child size="lg"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 w-64">
                            <Link :href="route('dashboard')">
                            Buka Dashboard
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white/50 dark:bg-gray-800/50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Kategori Produk
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Jelajahi berbagai kategori produk yang tersedia
                    </p>
                </div>

                <!-- Category Filter -->
                <div class="flex flex-wrap justify-center gap-3 mb-12">
                    <Button variant="outline" :class="[
                        'transition-all duration-200',
                        selectedCategory === null
                            ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'
                            : 'hover:bg-blue-50 dark:hover:bg-gray-700'
                    ]" @click="selectCategory(null)">
                        <TagIcon class="w-4 h-4 mr-2" />
                        Semua Produk
                    </Button>
                    <Button v-for="category in categories" :key="category.id" variant="outline" :class="[
                        'transition-all duration-200',
                        selectedCategory === category.id
                            ? 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'
                            : 'hover:bg-blue-50 dark:hover:bg-gray-700'
                    ]" @click="selectCategory(category.id)">
                        {{ category.name }}
                        <Badge variant="secondary" class="ml-2">
                            {{ category.products_count }}
                        </Badge>
                    </Button>
                </div>

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card v-for="category in categories" :key="category.id"
                        class="group hover:shadow-lg transition-all duration-300 cursor-pointer border-0 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900"
                        @click="selectCategory(category.id)">
                        <CardHeader class="text-center pb-4">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <TagIcon class="w-8 h-8 text-white" />
                            </div>
                            <CardTitle class="text-lg">{{ category.name }}</CardTitle>
                            <CardDescription v-if="category.description">
                                {{ category.description }}
                            </CardDescription>
                        </CardHeader>
                        <CardFooter class="text-center pt-0">
                            <Badge variant="secondary" class="mx-auto">
                                {{ category.products_count }} produk
                            </Badge>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        {{selectedCategory === null ? 'Produk Unggulan' : categories.find(c => c.id ===
                            selectedCategory)?.name
                        }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ selectedCategory === null
                            ? 'Produk terpilih dengan kualitas terbaik'
                            : 'Produk dalam kategori yang dipilih'
                        }}
                    </p>
                </div>

                <!-- Products Grid -->
                <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card v-for="product in filteredProducts" :key="product.id"
                        class="group hover:shadow-xl transition-all duration-300 overflow-hidden border-0 bg-white dark:bg-gray-800">
                        <div class="aspect-square overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <img v-if="product.image_path" :src="product.image_path" :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <div v-else
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                                <ShoppingCartIcon class="w-16 h-16 text-gray-400 dark:text-gray-500" />
                            </div>
                        </div>

                        <CardHeader class="pb-2">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <CardTitle
                                        class="text-base line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ product.name }}
                                    </CardTitle>
                                    <CardDescription class="text-sm mt-1">
                                        {{ product.category.name }}
                                    </CardDescription>
                                </div>
                                <div class="flex items-center text-yellow-400">
                                    <StarIcon class="w-4 h-4 fill-current" />
                                    <StarIcon class="w-4 h-4 fill-current" />
                                    <StarIcon class="w-4 h-4 fill-current" />
                                    <StarIcon class="w-4 h-4 fill-current" />
                                    <StarIcon class="w-4 h-4" />
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="pt-0 pb-2">
                            <p v-if="product.description" class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ product.description }}
                            </p>
                            <div class="flex items-center justify-between mt-4">
                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatPrice(product.price) }}
                                </div>
                                <Badge :variant="product.current_stock > 0 ? 'default' : 'destructive'" class="text-xs">
                                    Stok: {{ product.current_stock }}
                                </Badge>
                            </div>
                        </CardContent>

                        <CardFooter class="pt-0">
                            <Button class="w-full bg-blue-600 hover:bg-blue-700 text-white"
                                :disabled="product.current_stock === 0">
                                <ShoppingCartIcon class="w-4 h-4 mr-2" />
                                {{ product.current_stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <ShoppingCartIcon class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Tidak ada produk
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Produk dalam kategori ini sedang tidak tersedia.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-600 to-purple-600">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Siap untuk memulai bisnis Anda?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Bergabunglah dengan ribuan penjual yang sudah mempercayai platform kami untuk mengelola bisnis
                    mereka.
                </p>
                <!-- <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Button v-if="!isAuthenticated" as-child size="lg" variant="secondary"
                        class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3">
                        <Link :href="route('register')">
                        Daftar Gratis
                        </Link>
                    </Button>
                    <Button v-else as-child size="lg" variant="secondary"
                        class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-3">
                        <Link :href="route('dashboard')">
                        Kelola Toko
                        </Link>
                    </Button>
                </div> -->
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <ShoppingCartIcon class="h-8 w-8 text-blue-400" />
                            <h3 class="text-xl font-bold">Cashier Store</h3>
                        </div>
                        <p class="text-gray-400 mb-4">
                            Platform kasir modern untuk membantu Anda mengelola bisnis retail dengan mudah dan efisien.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold mb-4">Produk</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Sistem Kasir</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Manajemen Stok</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Laporan Penjualan</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold mb-4">Support</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Bantuan</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Kontak</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2024 Cashier Store. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
