<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

defineProps({
    packages: {
        type: Array,
        required: true,
    },
});

const confirmingPackageDeletion = ref(false);
const packageToDelete = ref(null);

const confirmPackageDeletion = (pkg) => {
    packageToDelete.value = pkg;
    confirmingPackageDeletion.value = true;
};

const deletePackage = () => {
    router.delete(route('packages.destroy', packageToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingPackageDeletion.value = false;
    setTimeout(() => {
        packageToDelete.value = null;
    }, 250);
};
</script>

<template>
    <Head title="Packages" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Packages
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Manages the perfume-bar packages.
                    </p>
                </div>
                <Link
                    :href="route('packages.create')"
                    class="inline-flex items-center gap-2 rounded-full bg-brand-primary px-6 py-2.5 text-sm font-medium text-white shadow-ambient dark:shadow-none hover:bg-brand-primary/90 focus:outline-none transition-all duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Package
                </Link>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                  <span class="block sm:inline text-sm font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <!-- Macro Container -->
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border">
                    <h3 class="text-2xl font-semibold text-brand-primary dark:text-brand-cream mb-6">Active Packages</h3>
                    
                    <div v-if="packages.length === 0" class="text-center py-12 text-brand-muted dark:text-brand-cream/70">
                        <p>No active packages found. Click "Add Package" to create one.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Package Card -->
                        <div v-for="pkg in packages" :key="pkg.id" class="border border-brand-primary/10 dark:border-brand-dark-border rounded-2xl overflow-hidden flex flex-col group relative">
                            
                            <!-- Image Area -->
                            <div class="h-48 bg-brand-cream dark:bg-brand-dark-base flex items-center justify-center relative overflow-hidden">
                                <img v-if="pkg.images && pkg.images.length > 0 && pkg.images[0]" :src="pkg.images[0].startsWith('http') ? pkg.images[0] : '/storage/' + pkg.images[0]" class="w-full h-full object-cover" />
                                <span v-else class="text-brand-primary dark:text-brand-cream/40 font-medium italic">Image Placeholder</span>
                                
                                <!-- Hover Actions Overlay -->
                                <div class="absolute inset-0 bg-brand-primary/5 opacity-100 md:opacity-50 group-hover:opacity-100 transition-opacity duration-200 flex items-start justify-end p-3 gap-2 z-10">
                                     <button @click="confirmPackageDeletion(pkg)" class="bg-white dark:bg-brand-dark-surface text-red-500 rounded-full p-2 shadow-sm hover:text-red-700 transition-colors" title="Delete">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                         </svg>
                                     </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5 flex flex-col flex-1 bg-white dark:bg-brand-dark-surface">
                                <h4 class="font-semibold text-brand-primary dark:text-brand-cream text-lg">{{ pkg.name }}</h4>
                                <p class="text-brand-muted dark:text-brand-cream/70 text-xs mt-1 mb-4">
                                    Perfect for intimate celebrations and small gatherings.
                                </p>
                                
                                <div class="mt-auto flex items-center justify-between">
                                    <span class="font-medium text-brand-primary dark:text-brand-cream">
                                        Php. {{ parseFloat(pkg.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                    </span>
                                    <Link 
                                        :href="route('packages.edit', pkg.id)" 
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

        <Modal :show="confirmingPackageDeletion" @close="closeModal" maxWidth="md">
            <div class="p-8 bg-brand-cream dark:bg-brand-dark-surface flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center mb-4 shadow-sm border border-red-200 dark:border-red-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-brand-primary dark:text-brand-cream">
                    Delete Package?
                </h2>

                <p class="mt-2 text-sm text-brand-muted dark:text-brand-cream/70 max-w-sm">
                    This action cannot be undone. All data associated with this package will be permanently removed.
                </p>

                <div class="mt-8 flex gap-3 w-full justify-center">
                    <SecondaryButton @click="closeModal" class="rounded-full px-6 py-2"> Cancel </SecondaryButton>

                    <DangerButton
                        class="rounded-full px-6 py-2 shadow-ambient dark:shadow-none"
                        @click="deletePackage"
                    >
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
