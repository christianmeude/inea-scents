<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: Object,
    metrics: Object,
    popularPackages: Array,
    upcomingBookings: Array,
});

const filter = ref(props.filters.filter || 'all-time');

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
                        <h1 class="text-3xl font-medium text-brand-primary mb-1">Welcome Inea Scents!</h1>
                        <p class="text-brand-muted text-lg">Here's what's happening with your perfume bar.</p>
                    </div>
                    
                    <!-- Toggle Group Filter -->
                    <div class="flex bg-white border border-brand-primary/20 rounded-lg p-1 shadow-sm overflow-x-auto">
                        <button v-for="option in filterOptions" :key="option.value"
                                @click="updateFilter(option.value)"
                                :class="[
                                    'px-4 py-1.5 text-sm font-medium rounded-md transition-colors whitespace-nowrap',
                                    filter === option.value ? 'bg-brand-primary text-white shadow' : 'text-brand-primary hover:bg-brand-primary/10'
                                ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Metrics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white rounded-2xl p-6 shadow-ambient">
                        <h3 class="text-brand-primary font-medium text-base mb-3">Total Bookings</h3>
                        <p class="text-4xl text-brand-primary font-semibold">{{ metrics?.totalBookings || 0 }}</p>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-6 shadow-ambient">
                        <h3 class="text-brand-primary font-medium text-base mb-3">Total Revenue</h3>
                        <p class="text-4xl text-brand-primary font-semibold">Php. {{ metrics?.totalRevenue || '0' }}</p>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-6 shadow-ambient">
                        <h3 class="text-brand-primary font-medium text-base mb-3">Confirmed Events</h3>
                        <p class="text-4xl text-brand-primary font-semibold">{{ metrics?.confirmedEvents || 0 }}</p>
                    </div>
                </div>

                <!-- Popular Packages -->
                <h2 class="text-2xl font-medium text-brand-primary mb-4">Popular Packages</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div v-for="pkg in popularPackages" :key="pkg.id" class="bg-white rounded-2xl p-5 shadow-ambient flex flex-col justify-center">
                        <h3 class="text-brand-primary font-semibold text-sm mb-1">{{ pkg.name }}</h3>
                        <div class="flex items-baseline gap-2 mb-1">
                            <p class="text-3xl text-brand-primary font-semibold">{{ pkg.bookings_count }}</p>
                            <span v-if="pkg.new_today > 0" class="text-brand-muted text-xs font-medium tracking-wide">+{{ pkg.new_today }} new today</span>
                        </div>
                        <p class="text-brand-primary text-sm font-medium">Bookings</p>
                    </div>
                    
                    <!-- Empty State for packages -->
                    <div v-if="!popularPackages || popularPackages.length === 0" class="col-span-full py-8 text-center text-brand-muted bg-white/50 rounded-2xl border border-dashed border-brand-primary/30">
                        No packages booked yet in this period.
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                <h2 class="text-2xl font-medium text-brand-primary mb-4">Upcoming Bookings</h2>
                <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-ambient overflow-x-auto mb-8">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="text-brand-primary font-semibold text-sm border-b border-brand-primary/10">
                                <th class="pb-4 px-4 font-semibold">Customer</th>
                                <th class="pb-4 px-4 font-semibold">Package</th>
                                <th class="pb-4 px-4 font-semibold">Event Date</th>
                                <th class="pb-4 px-4 font-semibold">Status</th>
                                <th class="pb-4 px-4 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-brand-primary text-sm">
                            <tr v-for="booking in upcomingBookings" :key="booking.id" class="hover:bg-brand-primary/5 transition-colors group cursor-pointer border-b border-brand-primary/5 last:border-0" @click="router.visit(route('admin.bookings.index', { search: booking.customer }))">
                                <td class="py-4 px-4 font-medium">{{ booking.customer }}</td>
                                <td class="py-4 px-4">{{ booking.package }}</td>
                                <td class="py-4 px-4">{{ booking.event_date }}</td>
                                <td class="py-4 px-4">
                                    <span :class="{
                                        'text-yellow-500': booking.status === 'pending',
                                        'text-green-500': booking.status === 'confirmed',
                                        'text-gray-500': booking.status === 'completed',
                                        'text-red-500': booking.status === 'cancelled'
                                    }" class="text-xs font-bold uppercase tracking-wider">{{ booking.status }}</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <span class="text-brand-primary opacity-0 group-hover:opacity-100 transition-opacity text-sm font-medium">View &rarr;</span>
                                </td>
                            </tr>
                            <tr v-if="!upcomingBookings || upcomingBookings.length === 0">
                                <td colspan="5" class="py-8 text-center text-brand-muted">
                                    No upcoming bookings found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
