<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), active: route().current('dashboard') },
    { name: 'Bookings', href: '#', active: false },
    { name: 'Calendar', href: '#', active: false },
    { name: 'Packages', href: route('admin.packages.index'), active: route().current('admin.packages.*') },
    { name: 'Customers', href: '#', active: false },
    { name: 'Payments', href: '#', active: false },
    { name: 'Settings', href: '#', active: false },
];
</script>

<template>
    <div class="flex h-screen bg-gray-50 font-sans">
        <!-- Desktop Sidebar -->
        <aside class="w-64 flex-shrink-0 bg-white border-r border-gray-200 hidden md:flex md:flex-col">
            <!-- Logo area -->
            <div class="flex h-16 items-center px-6 border-b border-gray-200">
                <Link :href="route('dashboard')" class="flex items-center gap-3 text-burgundy-700">
                    <ApplicationLogo class="block h-8 w-auto fill-current" />
                    <span class="font-bold text-xl tracking-wide">INEA SCENTS</span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 mt-4">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        item.active 
                            ? 'bg-burgundy-50 text-burgundy-700 border-r-4 border-burgundy-600' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                        'group flex items-center px-3 py-2.5 text-sm font-medium rounded-l-md transition-colors'
                    ]"
                >
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User / Sign out area -->
            <div class="border-t border-gray-200 p-4">
                <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    Sign out
                </Link>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-200">
                <!-- Mobile Hamburger -->
                <div class="md:hidden flex items-center">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 md:hidden flex justify-center">
                    <span class="font-bold text-lg text-burgundy-700">INEA SCENTS</span>
                </div>

                <!-- Right side (User menu on Desktop) -->
                <div class="hidden md:flex items-center ml-auto">
                    <div class="relative ms-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
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
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden bg-white border-b border-gray-200">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink v-for="item in navigation" :key="item.name" :href="item.href" :active="item.active">
                        {{ item.name }}
                    </ResponsiveNavLink>
                </div>
                <div class="border-t border-gray-200 pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white border-b border-gray-200">
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
