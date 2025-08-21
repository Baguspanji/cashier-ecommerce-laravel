<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem, User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Menu,
    PackageIcon,
    TagIcon,
    ShoppingCartIcon,
    BarChart3Icon,
    TrendingUpIcon
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth as { user: User });

const isCurrentRoute = computed(() => (url: string) => {
    // Handle exact matches and path-based matches
    if (page.url === url) return true;

    // For nested routes, check if current URL starts with the nav item URL
    // but avoid matching root routes with everything
    if (url !== '/' && page.url.startsWith(url)) return true;

    return false;
});

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'bg-accent text-accent-foreground font-medium' : 'text-muted-foreground hover:text-foreground'),
);

const mainNavItems: NavItem[] = [
    // {
    //     title: 'Dashboard',
    //     href: '/dashboard',
    //     icon: LayoutGrid,
    // },
    {
        title: 'POS',
        href: route('dashboard'),
        icon: ShoppingCartIcon,
    },
    {
        title: 'Kategori',
        href: route('categories.index'),
        icon: TagIcon,
    },
    {
        title: 'Produk',
        href: route('products.index'),
        icon: PackageIcon,
    },
    {
        title: 'Stok',
        href: route('stock.overview'),
        icon: TrendingUpIcon,
    },
    {
        title: 'Transaksi',
        href: route('transactions.index'),
        icon: BarChart3Icon,
    },
];
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link v-for="item in mainNavItems" :key="item.title" :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                                        :class="activeItemStyles(item.href)">
                                    <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                    {{ item.title }}
                                    </Link>
                                </nav>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1 lg:justify-center">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index"
                                class="relative flex h-full items-center">
                                <Link
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3 transition-colors']"
                                    :href="item.href">
                                <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                {{ item.title }}
                                </Link>
                                <div v-if="isCurrentRoute(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-primary">
                                </div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary">
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar"
                                        :alt="auth.user.name" />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
