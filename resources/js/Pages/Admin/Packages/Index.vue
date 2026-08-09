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
            <div class="flex items-end justify-between border-b border-brand-primary/10 pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary">
                        Packages
                    </h2>
                    <p class="text-brand-muted text-sm mt-1">
                        Manages the perfume-bar packages.
                    </p>
                </div>
                <Link
                    :href="route('admin.packages.create')"
                    class="rounded-full bg-transparent border border-brand-primary px-5 py-2 text-sm font-medium text-brand-primary hover:bg-brand-primary/5 focus:outline-none transition-all duration-200"
                >
                    Add Package +
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                  <span class="block sm:inline text-sm font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <!-- Macro Container -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-ambient border border-brand-primary/10">
                    <h3 class="text-2xl font-semibold text-brand-primary mb-6">Active Packages</h3>
                    
                    <div v-if="packages.length === 0" class="text-center py-12 text-brand-muted">
                        <p>No active packages found. Click "Add Package" to create one.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Package Card -->
                        <div v-for="pkg in packages" :key="pkg.id" class="border border-brand-primary/10 rounded-2xl overflow-hidden flex flex-col group relative">
                            
                            <!-- Placeholder Image Area -->
                            <div class="h-48 bg-brand-cream flex items-center justify-center relative">
                                <span class="text-brand-primary/40 font-medium italic">Image Placeholder</span>
                                
                                <!-- Hover Actions Overlay -->
                                <div class="absolute inset-0 bg-brand-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-start justify-end p-3 gap-2">
                                     <Link :href="route('admin.packages.destroy', pkg.id)" method="delete" as="button" class="bg-white text-red-500 rounded-full p-2 shadow-sm hover:text-red-700 transition-colors" title="Delete">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                         </svg>
                                     </Link>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5 flex flex-col flex-1 bg-white">
                                <h4 class="font-semibold text-brand-primary text-lg">{{ pkg.name }}</h4>
                                <p class="text-brand-muted text-xs mt-1 mb-4">
                                    Perfect for intimate celebrations and small gatherings.
                                </p>
                                
                                <div class="mt-auto flex items-center justify-between">
                                    <span class="font-medium text-brand-primary">
                                        Php. {{ parseFloat(pkg.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                    </span>
                                    <Link 
                                        :href="route('admin.packages.edit', pkg.id)" 
                                        class="bg-brand-primary text-white text-xs font-medium px-6 py-2 rounded-full hover:opacity-90 transition-opacity"
                                    >
                                        Modify
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
