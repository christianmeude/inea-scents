<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ViewBookingModal from './Admin/Bookings/Partials/ViewBookingModal.vue';

const props = defineProps({
    filters: Object,
    metrics: Object,
    popularPackages: Array,
    upcomingBookings: Array,
});

const filter = ref(props.filters.filter || 'all-time');

const isViewModalOpen = ref(false);
const selectedBooking = ref(null);

const openViewModal = (booking) => {
    selectedBooking.value = booking;
    isViewModalOpen.value = true;
};

const filterOptions = [
    { label: 'Daily', value: 'daily' },
    { label: 'Weekly', value: 'weekly' },
    { label: 'Monthly', value: 'monthly' },
    { label: 'Yearly', value: 'yearly' },
    { label: 'All Time', value: 'all-time' },
];

const updateFilter = (value) => {
    filter.value = value;
    router.get(route('dashboard'), { filter: value }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- The header slot can be empty or omitted since we have our custom welcome area -->
        
        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Welcome & Filter Row -->
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                    <div>
                        <h1 class="text-3xl font-medium text-brand-primary dark:text-brand-cream mb-1">Welcome Inea Scents!</h1>
                        <p class="text-brand-muted dark:text-brand-cream/70 text-lg">Here's what's happening with your perfume bar.</p>
                    </div>
                    
                    <!-- Toggle Group Filter -->
                    <div class="flex bg-white dark:bg-brand-dark-surface border border-brand-primary/20 dark:border-brand-dark-border rounded-full p-1 shadow-sm overflow-x-auto">
                        <button v-for="option in filterOptions" :key="option.value"
                                @click="updateFilter(option.value)"
                                :class="[
                                    'px-4 py-1.5 text-sm font-medium rounded-full transition-colors whitespace-nowrap',
                                    filter === option.value ? 'bg-brand-primary text-white shadow' : 'text-brand-primary dark:text-brand-cream hover:bg-brand-primary/10'
                                ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white dark:bg-brand-dark-surface rounded-2xl p-6 shadow-ambient dark:shadow-none">
                        <h3 class="text-brand-primary dark:text-brand-cream font-medium text-base mb-3">Total Bookings</h3>
                        <p class="text-4xl text-brand-primary dark:text-brand-cream font-semibold">{{ metrics?.totalBookings || 0 }}</p>
                    </div>
                    
                    <div class="bg-white dark:bg-brand-dark-surface rounded-2xl p-6 shadow-ambient dark:shadow-none">
                        <h3 class="text-brand-primary dark:text-brand-cream font-medium text-base mb-3">Total Revenue</h3>
                        <p class="text-4xl text-brand-primary dark:text-brand-cream font-semibold">Php. {{ metrics?.totalRevenue || '0' }}</p>
                    </div>
                    
                    <div class="bg-white dark:bg-brand-dark-surface rounded-2xl p-6 shadow-ambient dark:shadow-none">
                        <h3 class="text-brand-primary dark:text-brand-cream font-medium text-base mb-3">Confirmed Events</h3>
                        <p class="text-4xl text-brand-primary dark:text-brand-cream font-semibold">{{ metrics?.confirmedEvents || 0 }}</p>
                    </div>
                </div>

                <!-- Popular Packages -->
                <h2 class="text-2xl font-medium text-brand-primary dark:text-brand-cream mb-4">Popular Packages</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div v-for="pkg in popularPackages" :key="pkg.id" class="bg-white dark:bg-brand-dark-surface rounded-2xl p-5 shadow-ambient dark:shadow-none flex flex-col justify-center">
                        <h3 class="text-brand-primary dark:text-brand-cream font-semibold text-sm mb-1">{{ pkg.name }}</h3>
                        <div class="flex items-baseline gap-2 mb-1">
                            <p class="text-3xl text-brand-primary dark:text-brand-cream font-semibold">{{ pkg.bookings_count }}</p>
                            <span v-if="pkg.new_today > 0" class="text-brand-muted dark:text-brand-cream/70 text-xs font-medium tracking-wide">+{{ pkg.new_today }} new today</span>
                        </div>
                        <p class="text-brand-primary dark:text-brand-cream text-sm font-medium">Bookings</p>
                    </div>
                    
                    <!-- Empty State for packages -->
                    <div v-if="!popularPackages || popularPackages.length === 0" class="col-span-full py-8 text-center text-brand-muted dark:text-brand-cream/70 bg-white dark:bg-brand-dark-surface/50 rounded-2xl border border-dashed border-brand-primary/30">
                        No packages booked yet in this period.
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                <h2 class="text-2xl font-medium text-brand-primary dark:text-brand-cream mb-4">Upcoming Bookings</h2>
                <div class="bg-white dark:bg-brand-dark-surface rounded-2xl p-4 sm:p-6 shadow-ambient dark:shadow-none overflow-x-auto mb-8">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="text-brand-primary dark:text-brand-cream font-semibold text-sm border-b border-brand-primary/10 dark:border-brand-dark-border">
                                <th class="pb-4 px-4 font-semibold">Customer</th>
                                <th class="pb-4 px-4 font-semibold">Package</th>
                                <th class="pb-4 px-4 font-semibold">Event Date</th>
                                <th class="pb-4 px-4 font-semibold">Status</th>
                                <th class="pb-4 px-4 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-brand-primary dark:text-brand-cream text-sm">
                            <tr v-for="booking in upcomingBookings" :key="booking.id" 
                                class="hover:bg-brand-primary/5 dark:hover:bg-brand-dark-accent focus-within:bg-brand-primary/5 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-primary/20 transition-colors group cursor-pointer border-b border-brand-primary/5 last:border-0" 
                                tabindex="0"
                                @click="openViewModal(booking)"
                                @keyup.enter="openViewModal(booking)">
                                <td class="py-4 px-4 font-medium">{{ booking.customer_name }}</td>
                                <td class="py-4 px-4">{{ booking.package ? booking.package.name : 'N/A' }}</td>
                                <td class="py-4 px-4">{{ new Date(booking.event_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</td>
                                <td class="py-4 px-4">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20': booking.status.toLowerCase() === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20': booking.status.toLowerCase() === 'confirmed',
                                        'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300 dark:border-white/20': booking.status.toLowerCase() === 'completed',
                                        'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20': booking.status.toLowerCase() === 'cancelled'
                                    }" class="inline-block w-28 text-center px-2 py-0.5 border border-transparent rounded-full text-xs font-bold uppercase tracking-wider">{{ booking.status }}</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button v-if="booking.status.toLowerCase() === 'pending'" 
                                                @click.stop="router.patch(route('admin.bookings.approve', booking.id), {}, { preserveScroll: true })" 
                                                class="text-green-700 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium opacity-0 group-focus-within:opacity-100 group-hover:opacity-100 transition-opacity bg-green-100 dark:bg-green-500/10 hover:bg-green-200 dark:hover:bg-green-500/20 border border-transparent dark:border-green-500/20 px-3 py-1 rounded-full text-xs">
                                            Approve
                                        </button>
                                        <span class="text-brand-primary dark:text-brand-cream opacity-0 group-focus-within:opacity-100 group-hover:opacity-100 transition-opacity text-sm font-medium">View &rarr;</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!upcomingBookings || upcomingBookings.length === 0">
                                <td colspan="5" class="py-8 text-center text-brand-muted dark:text-brand-cream/70">
                                    No upcoming bookings found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <ViewBookingModal 
            :show="isViewModalOpen" 
            :booking="selectedBooking" 
            @close="isViewModalOpen = false" 
        />
    </AuthenticatedLayout>
</template>
