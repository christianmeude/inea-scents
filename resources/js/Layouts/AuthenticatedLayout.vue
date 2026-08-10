<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const currentTheme = ref('system');

onMounted(() => {
    if (!('theme' in localStorage)) {
        currentTheme.value = 'system';
    } else {
        currentTheme.value = localStorage.theme;
    }
});

const setTheme = (theme) => {
    currentTheme.value = theme;
    if (theme === 'system') {
        localStorage.removeItem('theme');
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    } else {
        localStorage.theme = theme;
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
    { name: 'Bookings', href: route('admin.bookings.index'), active: route().current('admin.bookings.*') },
    { name: 'Calendar', href: route('admin.calendar.index'), active: route().current('admin.calendar.*') },
    { name: 'Packages', href: route('admin.packages.index'), active: route().current('admin.packages.*') },
    { name: 'Customers', href: '#', active: false },
    { name: 'Payments', href: '#', active: false },
    { name: 'Settings', href: '#', active: false },
];
</script>

<template>
    <div class="flex h-screen bg-brand-cream dark:bg-brand-dark-base font-sans transition-colors duration-200">
        <!-- Desktop Sidebar -->
        <aside class="w-64 flex-shrink-0 bg-white dark:bg-brand-dark-surface border-r border-brand-primary/20 dark:border-brand-dark-border hidden md:flex md:flex-col shadow-sm z-10 relative transition-colors duration-200">
            <!-- Logo area -->
            <div class="flex h-20 items-center px-6 justify-center">
                <Link :href="route('dashboard')" class="flex items-center gap-2 text-brand-primary dark:text-brand-cream">
                    <span class="text-2xl tracking-widest uppercase font-logo-sans font-bold">INEA</span>
                    <span class="text-3xl capitalize font-normal font-logo-script -ml-1.5 mt-1">Scents</span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-3 mt-2">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        item.active 
                            ? 'bg-brand-primary dark:bg-brand-dark-accent text-white border-transparent' 
                            : 'bg-white dark:bg-brand-dark-surface text-brand-primary dark:text-brand-cream border-gray-200 dark:border-brand-dark-border hover:border-brand-primary/50 dark:hover:border-brand-dark-border hover:bg-brand-primary/5 dark:hover:bg-brand-dark-accent',
                        'group flex items-center px-5 py-2.5 text-sm font-medium rounded-full border transition-all duration-200'
                    ]"
                >
                    {{ item.name }}
                </Link>
            </nav>

            <!-- Theme Toggle -->
            <div class="px-4 mb-2">
                <div class="flex items-center justify-between bg-brand-cream dark:bg-brand-dark-base rounded-full p-1 border border-brand-primary/10 dark:border-brand-dark-border">
                    <button @click="setTheme('light')" :class="currentTheme === 'light' ? 'bg-white dark:bg-brand-dark-surface shadow-sm text-brand-primary dark:text-brand-cream' : 'text-brand-primary dark:text-brand-cream/60 dark:text-brand-cream/60 hover:text-brand-primary dark:text-brand-cream dark:hover:text-brand-cream'" class="flex-1 py-1.5 text-xs font-medium rounded-full transition-all">Light</button>
                    <button @click="setTheme('dark')" :class="currentTheme === 'dark' ? 'bg-white dark:bg-brand-dark-surface shadow-sm text-brand-primary dark:text-brand-cream' : 'text-brand-primary dark:text-brand-cream/60 dark:text-brand-cream/60 hover:text-brand-primary dark:text-brand-cream dark:hover:text-brand-cream'" class="flex-1 py-1.5 text-xs font-medium rounded-full transition-all">Dark</button>
                    <button @click="setTheme('system')" :class="currentTheme === 'system' ? 'bg-white dark:bg-brand-dark-surface shadow-sm text-brand-primary dark:text-brand-cream' : 'text-brand-primary dark:text-brand-cream/60 dark:text-brand-cream/60 hover:text-brand-primary dark:text-brand-cream dark:hover:text-brand-cream'" class="flex-1 py-1.5 text-xs font-medium rounded-full transition-all">System</button>
                </div>
            </div>

            <!-- User / Sign out area -->
            <div class="p-4 mb-4 px-4">
                <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center justify-center px-5 py-2.5 text-sm font-medium text-brand-primary dark:text-brand-cream border border-gray-200 dark:border-brand-dark-border rounded-full hover:border-brand-primary/50 dark:hover:border-brand-dark-border hover:bg-brand-primary/5 dark:hover:bg-brand-dark-accent transition-all duration-200">
                    SIGN OUT
                </Link>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Topbar (Mobile only mostly, but kept for user dropdown on desktop) -->
            <header class="bg-transparent h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 relative">
                <!-- Mobile Hamburger -->
                <div class="md:hidden flex items-center">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" type="button" class="text-brand-primary dark:text-brand-cream hover:text-brand-primary dark:text-brand-cream/80 dark:hover:text-brand-cream/80 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 md:hidden flex justify-center">
                    <span class="text-xl text-brand-primary dark:text-brand-cream tracking-widest flex items-center font-logo-sans font-bold">
                        INEA 
                        <span class="text-2xl capitalize font-normal ml-1 font-logo-script mt-0.5">Scents</span>
                    </span>
                </div>

                <!-- Right side (User menu on Desktop) -->
                <div class="hidden md:flex items-center ml-auto">
                    <div class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-full">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-full border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark-surface px-4 py-2 text-sm font-medium leading-4 text-brand-primary dark:text-brand-cream transition duration-150 ease-in-out hover:bg-brand-primary/5 dark:hover:bg-brand-dark-accent focus:outline-none"
                                    >
                                        {{ $page.props.auth.user.name }}

                                        <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Mobile Navigation Menu -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden bg-white dark:bg-brand-dark-surface border-b border-brand-primary/20 dark:border-brand-dark-border absolute w-full z-20">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink v-for="item in navigation" :key="item.name" :href="item.href" :active="item.active">
                        {{ item.name }}
                    </ResponsiveNavLink>
                </div>
                <div class="border-t border-brand-primary/10 dark:border-brand-dark-border pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-brand-primary dark:text-brand-cream">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-brand-muted dark:text-brand-cream/70">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Page Heading (Removed background to let cream flow, just content) -->
            <header v-if="$slots.header" class="pt-6 pb-2">
                <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
