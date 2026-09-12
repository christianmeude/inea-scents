<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Chip from '@/Components/Chip.vue';
import DataTable from '@/Components/DataTable.vue';
import { statusTone } from '@/Components/tones.js';
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
        route('admin.bookings.index'),
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

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

const columns = [
    { key: 'reference', label: 'Booking ID' },
    { key: 'name', label: 'Name' },
    { key: 'package', label: 'Package' },
    { key: 'date', label: 'Date' },
    { key: 'status', label: 'Status' },
    { key: 'action', label: 'Action' },
];
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
                    class="inline-flex items-center gap-2 rounded-full bg-brand-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-primary/90 focus:outline-none transition-colors duration-200"
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
                <div class="bg-white dark:bg-brand-dark-surface rounded-3xl p-6 sm:p-8 border border-brand-primary/10 dark:border-brand-dark-border">
                    <DataTable
                        :columns="columns"
                        :items="bookings.data"
                        :links="bookings.links"
                        empty-text="No bookings found."
                    >
                        <template #filters>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search reference or name"
                                class="w-72 px-4 py-2 border border-brand-primary/20 dark:border-brand-dark-border rounded-lg text-sm text-brand-primary dark:text-brand-cream focus:outline-none focus:border-brand-primary bg-white dark:bg-brand-dark-surface placeholder-brand-muted/40 transition-colors"
                            >
                        </template>

                        <template #row="{ item: booking }">
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">
                                {{ booking.booking_reference }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ booking.customer_name }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream">
                                {{ booking.pax ? booking.pax + ' PAX ' : '' }}{{ booking.package?.name }}
                            </td>
                            <td class="py-4 px-4 font-medium text-brand-primary dark:text-brand-cream whitespace-nowrap">
                                {{ formatDate(booking.event_date) }}
                            </td>
                            <td class="py-4 px-4">
                                <Chip :tone="statusTone(booking.status)">
                                    {{ booking.status }}
                                </Chip>
                            </td>
                            <td class="py-4 px-4">
                                <button @click="openViewModal(booking)" class="text-cyan-500 font-medium hover:text-cyan-600 transition-colors whitespace-nowrap inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    View Details
                                </button>
                            </td>
                        </template>
                    </DataTable>
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
