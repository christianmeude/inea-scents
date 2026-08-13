<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ViewBookingModal from './Partials/ViewBookingModal.vue';
import CreateBookingModal from './Partials/CreateBookingModal.vue';

const props = defineProps({
    bookings: Object,
    packages: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
    router.get(
        route('bookings.index'),
        { search: value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

const isViewModalOpen = ref(false);
const selectedBooking = ref(null);

const isCreateModalOpen = ref(false);

const openViewModal = (booking) => {
    selectedBooking.value = booking;
    isViewModalOpen.value = true;
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Confirmed': return 'text-green-500 dark:text-green-400 font-semibold';
        case 'Pending': return 'text-yellow-500 dark:text-yellow-400 font-semibold';
        case 'Unavailable': return 'text-red-500 dark:text-red-400 font-semibold';
        case 'Cancelled': return 'text-gray-500 dark:text-gray-400 font-semibold';
        default: return 'text-gray-700 dark:text-gray-300 font-semibold';
    }
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};
</script>

<template>
    <Head title="Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between border-b border-brand-primary/10 dark:border-brand-dark-border pb-4 mb-4">
                <div>
                    <h2 class="text-3xl font-semibold text-brand-primary dark:text-brand-cream">
                        Bookings
                    </h2>
                    <p class="text-brand-muted dark:text-brand-cream/70 text-sm mt-1">
                        Manage customer reservations and event requests.
                    </p>
                </div>
                <button
                    @click="isCreateModalOpen = true"
                    class="inline-flex items-center gap-2 rounded-full bg-brand-primary px-6 py-2.5 text-sm font-medium text-white shadow-ambient dark:shadow-none hover:bg-brand-primary/90 focus:outline-none transition-all duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Booking
                </button>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl">
                
                <!-- Macro Container -->
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 shadow-ambient dark:shadow-none border border-brand-primary/10 dark:border-brand-dark-border relative">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-2xl font-semibold text-brand-primary dark:text-brand-cream">Manage Booking</h3>
                        
                        <!-- Search Bar -->
                        <div class="flex items-center gap-4">
                            <div class="relative w-72">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-brand-primary dark:text-brand-cream text-sm font-medium">
                                    Search Item Here:
                                </span>
                                <input 
                                    type="text" 
                                    v-model="search"
                                    placeholder='"BOOKING-123"' 
                                    class="w-full pl-36 pr-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40 transition-colors"
                                >
                            </div>
                            <button class="text-brand-primary dark:text-brand-cream hover:text-brand-primary dark:text-brand-cream/70 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Polished Table -->
                    <div class="overflow-x-auto pb-4">
                        <table class="w-full text-left border-collapse border-b border-brand-primary/10 dark:border-brand-dark-border">
                            <thead>
                                <tr class="text-brand-primary dark:text-brand-cream text-xs font-bold border-b border-brand-primary/30">
                                    <th class="py-4 px-6">Booking ID</th>
                                    <th class="py-4 px-6">Name</th>
                                    <th class="py-4 px-6">Package</th>
                                    <th class="py-4 px-6">Date</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr 
                                    v-for="(booking, index) in bookings.data" 
                                    :key="booking.id"
                                    :class="[
                                        'transition-colors duration-150',
                                        index % 2 === 0 ? 'bg-transparent' : 'bg-gray-100 dark:bg-white/5 rounded-xl'
                                    ]"
                                    style="border-spacing: 0 4px;"
                                >
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap" :class="{ 'rounded-l-xl': index % 2 !== 0 }">
                                        {{ booking.booking_reference }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ booking.customer_name }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ booking.pax ? booking.pax + ' PAX ' : '' }}{{ booking.package?.name }}
                                    </td>
                                    <td class="py-5 px-6 font-medium text-brand-primary dark:text-brand-cream">
                                        {{ formatDate(booking.event_date) }}
                                    </td>
                                    <td class="py-5 px-6">
                                        <span :class="getStatusColor(booking.status)">
                                            {{ booking.status }}
                                        </span>
                                    </td>
                                    <td class="py-5 px-6" :class="{ 'rounded-r-xl': index % 2 !== 0 }">
                                        <button @click="openViewModal(booking)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination (basic placeholder since we didn't specify a custom design for it) -->
                    <div class="mt-6 flex justify-end" v-if="bookings.links && bookings.links.length > 3">
                        <div class="flex gap-1">
                            <Link 
                                v-for="(link, k) in bookings.links" 
                                :key="k" 
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1 rounded-md border text-sm"
                                :class="link.active ? 'bg-brand-primary text-white border-brand-primary' : 'border-gray-200 dark:border-brand-dark-border text-gray-500 hover:bg-gray-50'"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ViewBookingModal 
            :show="isViewModalOpen" 
            :booking="selectedBooking" 
            @close="isViewModalOpen = false" 
        />
        
        <CreateBookingModal 
            :show="isCreateModalOpen" 
            :packages="packages"
            @close="isCreateModalOpen = false" 
        />
    </AuthenticatedLayout>
</template>

<style scoped>
/* To get the rounded corners on the tr elements to show up */
table {
    border-collapse: separate;
    border-spacing: 0 4px;
}
</style>
