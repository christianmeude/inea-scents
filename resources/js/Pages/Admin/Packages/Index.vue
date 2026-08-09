<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    packages: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Packages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Packages Management
                </h2>
                <Link
                    :href="route('admin.packages.create')"
                    class="rounded-md bg-burgundy-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-burgundy-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-burgundy-600"
                >
                    Add Package
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                  <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="pkg in packages" :key="pkg.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ pkg.name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    ${{ parseFloat(pkg.price).toFixed(2) }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <Link :href="route('admin.packages.edit', pkg.id)" class="text-burgundy-600 hover:text-burgundy-900 mr-4">Edit<span class="sr-only">, {{ pkg.name }}</span></Link>
                                    <Link :href="route('admin.packages.destroy', pkg.id)" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete<span class="sr-only">, {{ pkg.name }}</span></Link>
                                </td>
                            </tr>
                            <tr v-if="packages.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No packages found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
